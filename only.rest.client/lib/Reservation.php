<?php

namespace Only\Rest\Client;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use CSaleOrder;
use Exception;

class Reservation extends Main
{
    protected static $instance = null;

    protected function __construct()
    {
        parent::__construct();
        $this->baseUrl .= 'Reservation/';
    }

    public function calculate($arParams)
    {
        $required = ['arrivalDate', 'departureDate', 'adults'];
        if (extract(get_variables($arParams, $required)) !== count($required))
            return ['status' => 'error', 'message' => 'Отсутствуют обязательные параметры'];

        $data = [
            'PropertyId' => 1, //Идентификатор объекта (гостиницы)
            'RateId' => $arParams['rateId'] ?? Option::get(Main::MODULE_ID, 'rate'), //Идентификатор тарифа
            'DateRange' => [ //Интервал бронирования
                'DateTimeFrom' => date('c', $arrivalDate),
                'DateTimeTo' => date('c', $departureDate),
            ],
            'Layout' => [ //Информация о числе гостей
                'IsUndefined' => false,
                'AdultCount' => $adults,
                'Child1Count' => isset($children) ? $children : 0,
                'Child2Count' => isset($teens) ? $teens : 0,
                'PutOnExtraBed' => false,
            ],
        ];
        set_data($data, $arParams, ['RoomTypeIds' => 'roomTypeIds', 'Layout.Child1Count' => 'teens', 'Layout.Child2Count' => 'children']);

        $response = $this->request(
            'POST',
            'Calculate',
            $data
        );
        if (empty($response))
            return ['status' => 'error', 'message' => 'Сервис временно недоступен'];

        $response['RoomTypes'] = array_filter($response['RoomTypes'], function ($roomType) {
            return $roomType['IsAvailable'] && !$roomType['StayPrice']['UnavailabilityReasonDetails'];
        });
        $response['RoomTypes'] = array_values($response['RoomTypes']);

        $result = array_map(function ($roomType) {
            return [
                'RoomTypeId' => $roomType['StayPrice']['RoomTypeId'],
                'price' => [
                    'firstDay' => $roomType['StayPrice']['FirstDayPrice']['Amount'],
                    'total' => $roomType['StayPrice']['TotalStayPrice']['Amount'],
                ],
            ];
        }, $response['RoomTypes']);

        return $result ?? [];
    }

    public function getPricing($arParams)
    {
        $required = ['arrivalDate', 'departureDate'];
        if (extract(get_variables($arParams, $required)) !== count($required))
            return ['status' => 'error', 'message' => 'Отсутствуют обязательные параметры'];

        $data = [
            'PropertyId' => 1,
            'RoomCount' => 1,
            'DateRange' => [
                'StayChargeUnit' => [
                    'Code' => 'H',
                    'Name' => 'Hour',
                ],
                'DateTimeFrom' => date('c', $arrivalDate),
                'DateTimeTo' => date('c', $departureDate),
            ],
            'SummaryLayout' => [
                'IsUndefined' => false,
                'AdultCount' => $arParams['adults'] ?? 1,
                'Child1Count' => $arParams['children']  ?? 0,
                'Child2Count' => $arParams['teens']  ?? 0,
            ],
        ];
        set_data($data, $arParams, ['RateId' => 'rateId', 'RoomTypeId' => 'roomTypeId', 'SummaryLayout.Child1Count' => 'extraPlace']);

        $request = $this->request(
            'POST',
            'GetPricing',
            $data
        );

        $request['Rates'] = array_combine(array_column($request['Rates'], 'RateId'), array_values($request['Rates']));
        $request['Rates'] = array_map(function ($arElem) {
            $arElem['Details'] = array_combine(array_column($arElem['Details'], 'RoomTypeId'), array_values($arElem['Details']));
            return $arElem;
        }, $request['Rates']);

        return $request;
    }


    /**
     * Создает бронь по массиву параметров, валидируя их
     *
     * @param array $arParams
     * @param bool $final
     * @return array
     * @throws Exception
     */
    public function createReservation(array $arParams, $final = false): array
    {
        $data = [
            'PropertyId' => 1,
        ];
        foreach ($arParams['reservations'] as $reservation) {
            $required = ['arrivalDate', 'departureDate', 'adults', 'roomTypeId', 'guests'];
            if (extract(get_variables($reservation, $required)) !== count($required)) continue;

            $reservationParam = [
                'RateId' => $reservation['rateId'] ?? Option::get(Main::MODULE_ID, 'rate'),
                'RoomTypeId' => $roomTypeId,
                'DateRange' => [
                    'DateTimeFrom' => date('c', $arrivalDate),
                    'DateTimeTo' => date('c', $departureDate),
                ],
                'Layout' => [
                    'IsUndefined' => false,
                    'AdultCount' => $adults,
                    'Child1Count' => isset($children) ? $children : 0,
                    'Child2Count' => isset($teens) ? $teens : 0,
                ],
                'MarketingData' => [
                    'TrackCodeId' => 3,
                    'OpenCodeId' => 1,
                    'BookingSourceId' => 10002,
                ],
                'GuaranteeTypeId' => 1,
            ];
            if ($final)
                foreach ($reservation['guests'] as $key => &$guest) {
                    $guest = [];
                    set_data($guest, $guests[$key], ['FirstName' => 'name', 'MiddleName' => 'patronymic', 'LastName' => 'surname', 'PhoneNumber' => 'phone', 'Email' => 'email']);
                }
            set_data($reservationParam, $reservation, ['Layout.Child1Count' => 'teens', 'Layout.Child2Count' => 'children', 'Notes' => 'notes', 'Guests' => 'guests']);
            $data['Reservations'][] = $reservationParam;
        }
        if (!isset($data['Reservations']))
            return [];

        if ($final)
            foreach ($arParams['reservations'] as $reservation)
                $this->cancel($reservation['orderId']);

        return $this->request(
            'POST',
            'CreateReservation',
            $data
        );
    }

    public function cancel($orderId)
    {
        if (!Loader::includeModule('sale'))
            return ['message' => 'Не установлен модуль магазина', 'status' => 'error'];

        $order = CSaleOrder::GetByID($orderId) ?: [];
        if ($order['COMMENTS'] ?? '')
            $result = $this->request(
                'POST',
                'CancelReservations',
                ['crsNumber' => $order['COMMENTS']],
                true
            );
        if (isset($result['Message']))
            return ['message' => $result['Message'], 'status' => 'error'];

        if ($order)
            (new CSaleOrder)->Delete($orderId);

        return [];
    }

    public function payment($data)
    {
        return $this->request(
            'POST',
            'AddPayment',
            $data
        );
    }

    public function getAvailability($arParams)
    {
        $required = ['dateFrom', 'dateTo'];
        if (extract(get_variables($arParams, $required)) !== count($required))
            return ['status' => 'error', 'message' => 'Отсутствуют обязательные параметры'];

        $arData = [
            'PropertyId' => 1,
            'DateRange' => [ //Интервал бронирования
                'DateTimeFrom' => date('c', $dateFrom),
                'DateTimeTo' => date('c', $dateTo),
            ],
        ];

        set_data($arData, $arParams, ['RoomTypeIds' => 'roomTypeIds', 'SplitInterval' => 'splitInterval']);

        return $this->request(
            'POST',
            'GetAvailability',
            $arData
        );
    }
}

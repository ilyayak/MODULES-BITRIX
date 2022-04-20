<?php

namespace Only\Rest\Client\Handlers;

use Bitrix\Main\Loader;

class addPricing
{
    public static function execute(\Bitrix\Sale\Order $order)
    {
        Loader::includeModule('sale');
        foreach ($order->getPaymentCollection() as $payment)
            /** @var \Bitrix\Sale\Payment $payment */
            if ($payment->getFields()->isChanged('PS_STATUS')
                && $payment->getField('PS_STATUS') === 'Y')
                \Only\Rest\Client\Reservation::getInstance()->payment([
                    'Payments' => [
                        [
                            'ReservationGenericNo' => $order->getField('COMMENTS'),
                            'Amount' => (int)$payment->getField('PS_SUM'),
                            'UniqueId' => 'WS:' . $order->getId(),
                            'TransactionCode' => 150,
                        ],
                    ],
                ]);
    }
}

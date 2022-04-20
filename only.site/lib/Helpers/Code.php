<?php


namespace Only\Site\Helpers;


use Bitrix\Iblock\ElementTable;
use CIBlockElement;
use CLang;

class Code
{
    /**
     * seconds
     */
    public const CODE_LIFETIME = 60;

    public function __construct()
    {
        $this->clearOldCodes();
    }

    public function add($code, $event)
    {
        $name = "{$event}#" . time();

        $activeCodesCount = ElementTable::getList(
            [
                'select'      => ['ID'],
                'filter'      => [
                    'IBLOCK_ID' => IB_CODES,
                    'ACTIVE' => 'Y',
                    '%NAME' => $event
                ],
                'count_total' => true
            ]
        )->getCount();

        if ($activeCodesCount > 0) {
            return "Срок активного кода еще не истек";
        }

        $obElement = new CIBlockElement();
        $ID = $obElement->Add(
            [
                "ACTIVE"    => "Y",
                "IBLOCK_ID" => IB_CODES,
                "NAME"      => $name,
                "CODE"      => $code,
            ]
        );

        if (empty($ID)) {
            return "Не удалось добавить код";
        }

        return "";
    }

    public function verify($code, $event)
    {
        $activeCodesCount = ElementTable::getList(
            [
                'select'      => ['ID'],
                'filter'      => [
                    'IBLOCK_ID' => IB_CODES,
                    'ACTIVE'    => 'Y',
                    '%NAME'     => $event,
                    'CODE'      => $code,
                ],
                'count_total' => true
            ]
        )->getCount();

        if ($activeCodesCount > 0) {
            return "";
        }

        return 'Неверный проверочный код';
    }

    /**
     * деактивирует все коды с истекшим сроком действия
     */
    public function clearOldCodes()
    {
        global $DB;

        $dateMin = date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL")), time() - self::CODE_LIFETIME);

        $arFilter = [
            "IBLOCK_ID"    => IB_CODES,
            "ACTIVE"       => "Y",
            '<TIMESTAMP_X' => $dateMin,
        ];

        $arExpiredCodes = ElementTable::getList(['filter' => $arFilter, 'select' => ['ID']])->fetchAll();
        foreach ($arExpiredCodes as $arCode) {
            $obElement = new CIBlockElement();
            $obElement->Update($arCode['ID'], ['ACTIVE' => 'N']);
        }
    }
}

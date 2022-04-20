<?php

namespace Only\Rest\Client\Agents;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Only\Rest\Client\Reservation;

class ClearOldOrders
{
    public static function execute()
    {
        if (Loader::includeModule('sale'))
            $orders = self::getOldOrders();
            if (!empty($orders)) {
                while ($order = $orders->fetch()) {
                    self::deleteOrder($order);
                }
            }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }

    private static function getOldOrders()
    {
        global $DB;
        $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('FULL'));

        $orders = false;
        try {
            $orders = Order::getList(['filter' => [
                '<DATE_UPDATE' => date($format, strtotime('-30 minutes')),
                '=PRICE' => 0,
            ]]);
        } catch (ArgumentException $e) {
            /* just continue */
        }

        return $orders;
    }

    private static function deleteOrder($order)
    {
        Reservation::getInstance()->cancel($order['ID']);
    }
}

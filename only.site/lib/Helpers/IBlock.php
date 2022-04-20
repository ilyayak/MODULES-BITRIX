<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 27.06.2018
 * Time: 11:44
 */

namespace Only\Site\Helpers;

use Bitrix\Main\Loader;

class IBlock
{

    public static function findIblock($code, $typeId = ''){
        $aItem = self::getIblock($code, $typeId);
        if ($aItem && isset($aItem['ID'])){
            return $aItem;
        }

        //$this->throwException(__METHOD__, "iblock not found");
    }

    public static function getIblockID($code, $typeId = ''){
        $aItem = self::getIblock($code, $typeId);
        if ($aItem && isset($aItem['ID'])){
            return $aItem['ID'];
        }

       // $this->throwException(__METHOD__, "iblock not found");
    }

    public static function getIblock($code, $typeId = '') {
        /** @compatibility filter or code */
        $filter = is_array($code) ? $code : array(
            '=CODE' => $code
        );

        if (!empty($typeId)) {
            $filter['=TYPE'] = $typeId;
        }

        $filter['CHECK_PERMISSIONS'] = 'N';

        Loader::includeModule('iblock');

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return \CIBlock::GetList(array('SORT' => 'ASC'), $filter)->Fetch();
    }

    public static function getIdElement ($elementCode, $iblickCode = '', $typeCode = '', $iblockId = 0) {
        $filter = ['CODE' => $elementCode];
        if (!empty($iblickCode))
            $filter['IBLOCK_CODE'] = $iblickCode;
        if (!empty($typeCode))
            $filter['IBLOCK_TYPE'] = $typeCode;
        if (!empty($iblockId))
            $filter['IBLOCK_ID'] = $iblockId;

        $arRes = \CIBlockElement::GetList (
            ["SORT"=>"ASC"],
            $filter,
            false,
            false,
            ['ID', 'IBLOCK_ID']
        );
        if ($element = $arRes->fetch()) {
            return $element['ID'];
        }
        return 0;
    }
}
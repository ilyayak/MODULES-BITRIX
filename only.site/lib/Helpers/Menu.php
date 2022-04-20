<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 28.06.2018
 * Time: 12:41
 */

namespace Only\Site\Helpers;


class Menu
{

    public static function buildTree($arItems, $iDepthLevel = 1)
    {
        $arTree = [];

        foreach ($arItems as $iKey => $arItem) {
            if ($arItem['DEPTH_LEVEL'] < $iDepthLevel)
                break;

            if ($arItem['DEPTH_LEVEL'] == $iDepthLevel) {

                if ($arItem['IS_PARENT'])
                    $arItem['ITEMS'] = self::buildTree(array_slice($arItems, $iKey + 1), $arItem['DEPTH_LEVEL'] + 1);

                $arTree[] = $arItem;
            }
        }

        return $arTree;
    }

}
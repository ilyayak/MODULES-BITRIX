<?
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class OnlyRestClientComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
             return $arParams;
    }
    public function executeComponent()
    {
        if (Loader::includeModule('only.rest.client')) {

        }else{
            ShowError('Не установлен модуль only.rest.client');
            return;
        }

        $this->includeComponentTemplate();
    }
}

<?
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Project: Site
 * Date: 2017-08-28
 * Time: 14:52:56
 */
class OnlyElementsListComponent extends \CBitrixComponent
{

    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

        $arParams['ONLY_ACTIVE'] = $this->checkParamsBoolVal($arParams['ONLY_ACTIVE']);
        $arParams['CHECK_DATES'] = $this->checkParamsBoolVal($arParams['CHECK_DATES']);
        $arParams['CHECK_PERMISSIONS'] = $this->checkParamsBoolVal($arParams['CHECK_PERMISSIONS']);

        $arParams['COUNT'] = intval($arParams['COUNT']);


        if (empty($arParams['SORT_BY']))
            $arParams['SORT_BY'] = 'SORT';

        if (empty($arParams['SORT_ORDER']))
            $arParams['SORT_ORDER'] = 'ASC';

        $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
        if ($arParams['CACHE_TIME'] <= 0)
            $arParams['CACHE_TIME'] = 604800;


        return $arParams;
    }

    protected function checkParamsBoolVal($sValue, $sDefault = 'N')
    {
        $sValue = trim($sValue);
        if (empty($sValue) || !in_array($sValue, ['N', 'Y']))
            $sValue = $sDefault;

        return $sValue;
    }

    /**
     * @return null
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        $this->arParams['NAVIGATION'] = $this->initPageNav();

        if ($this->StartResultCache()) {
            if (!Loader::includeModule('iblock')) {
                $this->abortResultCache();
                ShowError('Не установлен модуль iblock');
                return;
            }

            $this->arResult = \CIBlock::GetByID($this->arParams['IBLOCK_ID'])->Fetch();

            if (!$this->arResult) {
                $this->abortResultCache();
                ShowError('Инфоблок не найден');
                return;
            }

            $this->arResult['SECTION'] = $this->getSection();
            $this->arResult['ITEMS'] = $this->getItems();

            $this->includeComponentTemplate();
        }

        $this->addAdminButtons();
    }

    public function getSection()
    {
        if (empty($this->arParams['SECTION_ID']) && empty($this->arParams['SECTION_CODE']))
            return [];

        $arFilter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID']
        ];

        if ($this->arParams['ONLY_ACTIVE'] == 'Y') {
            $arFilter['ACTIVE'] = 'Y';
            $arFilter['GLOBAL_ACTIVE'] = 'Y';
        }

        if ($this->arParams['SECTION_ID'])
            $arFilter['ID'] = $this->arParams['SECTION_ID'];
        else
            $arFilter['CODE'] = $this->arParams['SECTION_CODE'];

        $arSection = CIBlockSection::GetList(
            ['ID' => 'ASC'],
            $arFilter,
            false,
            ['UF_*'],
            ['nTopCount' => 1]
        )->GetNext(true, false);

        if (!$arSection)
            return [];

        $obIPropValues = new Bitrix\Iblock\InheritedProperty\SectionValues($arSection['IBLOCK_ID'], $arSection['ID']);
        $arSection['IPROPERTY_VALUES'] = $obIPropValues->getValues();

        Tools::getFieldImageData(
            $arSection,
            ['PICTURE'],
            Tools::IPROPERTY_ENTITY_SECTION
        );

        Tools::getFieldImageData(
            $arSection,
            ['DETAIL_PICTURE'],
            Tools::IPROPERTY_ENTITY_SECTION
        );

        return $arSection;
    }

    public function getItems()
    {
        $rsItems = \CIBlockElement::GetList(
            $this->getOrder(),
            $this->getFilter(),
            false,
            $this->getNavParams()
        );

        if (!empty($this->arParams['PAGER_TEMPLATE'])) {
            $this->arResult['NAV_STRING'] = $rsItems->GetPageNavStringEx($navComponentObject, '', $this->arParams['PAGER_TEMPLATE'], false, $this);
            /** @var \CBitrixComponent $navComponentObject */
            $this->arResult['NAV_DATA'] = $navComponentObject->arResult;
        }

        $arItems = array();

        while ($obItem = $rsItems->GetNextElement(true, false)) {
            $arItem = $obItem->GetFields();
            $arItem['PROPERTIES'] = $obItem->GetProperties();

            foreach (['FROM', 'TO'] as $sDate) {
                if (!empty($this->arParams['ACTIVE_DATE_FORMAT']) && strlen($arItem['ACTIVE_' . $sDate]) > 0)
                    $arItem['DISPLAY_ACTIVE_' . $sDate] = \CIBlockFormatProperties::DateFormat($this->arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arItem['ACTIVE_' . $sDate], CSite::GetDateFormat()));
                else
                    $arItem['DISPLAY_ACTIVE_' . $sDate] = '';
            }

            $obIPropValues = new Bitrix\Iblock\InheritedProperty\ElementValues($arItem['IBLOCK_ID'], $arItem['ID']);
            $arItem['IPROPERTY_VALUES'] = $obIPropValues->getValues();

            Tools::getFieldImageData(
                $arItem,
                ['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
                Tools::IPROPERTY_ENTITY_ELEMENT
            );

            $this->addControls($arItem);

            $arItems[] = $arItem;
        }

        return $arItems;
    }

    public function getOrder()
    {
        $arSort = [$this->arParams['SORT_BY'] => $this->arParams['SORT_ORDER']];

        if (!empty($this->arParams['SORT_BY_2']) && !empty($this->arParams['SORT_ORDER_2']))
            $arSort[$this->arParams['SORT_BY_2']] = $this->arParams['SORT_ORDER_2'];

        return $arSort;
    }

    public function getFilter()
    {
        $arFilter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID']
        ];

        if ($this->arParams['ONLY_ACTIVE'] == 'Y')
            $arFilter['ACTIVE'] = 'Y';

        if ($this->arParams['CHECK_DATES'] == 'Y')
            $arFilter['ACTIVE_DATE'] = 'Y';

        if ($this->arParams['CHECK_PERMISSIONS'] == 'Y')
            $arFilter['CHECK_PERMISSIONS'] = 'Y';

        if ($this->arParams['SECTION_ID'])
            $arFilter['SECTION_ID'] = $this->arParams['SECTION_ID'];
        elseif ($this->arParams['SECTION_CODE'])
            $arFilter['SECTION_CODE'] = $this->arParams['SECTION_CODE'];

        if (!empty($this->arParams['FILTER']) && is_array($this->arParams['FILTER']))
            $arFilter = array_merge($arFilter, $this->arParams['FILTER']);

        return $arFilter;
    }

    /**
     * Добавление элементов управления
     * @param $arItem
     * @return bool
     */
    public function addControls(&$arItem)
    {
        if (!$arItem['IBLOCK_ID'] || !$arItem['ID']) return false;

        $arButtons = CIBlock::GetPanelButtons(
            $arItem['IBLOCK_ID'],
            $arItem['ID'],
            0,
            array('SECTION_BUTTONS' => false, 'SESSID' => false)
        );

        $arItem['EDIT_LINK'] = $arButtons['edit']['edit_element']['ACTION_URL'];
        $arItem['DELETE_LINK'] = $arButtons['edit']['delete_element']['ACTION_URL'];
        $arItem['CONTROL_ID'] = $this->getEditAreaId($arItem['ID']);
    }

    /**
     * Добавляем кнопки добавления|редактирования|удаления для элементов и разделов инфоблоков
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public function addAdminButtons()
    {
        global $APPLICATION;
        if ($APPLICATION->GetShowIncludeAreas() && Loader::includeModule('iblock')) {
            $arButtons = CIBlock::GetPanelButtons(
                $this->arResult['ID'],
                0,
                intval($this->arResult['SECTION']['ID']),
                array('SECTION_BUTTONS' => !empty($this->arResult['SECTION']))
            );

            $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
        }
    }

    /**
     * Загружает значения для свойств привязка к элементам и разделам
     * @todo доработать для разделов
     *
     * @param $arItem
     */
    public function loadPropertiesValues(&$arItem)
    {
        foreach ($arItem['PROPERTIES'] as &$arProp) {
            if (empty($arProp['VALUE']))
                continue;

            switch ($arProp['PROPERTY_TYPE']) {
                case'E':
                    $rsPropItems = CIBlockElement::GetList([], ['ID' => $arProp['VALUE'], 'ACTIVE' => 'Y']);
                    $arProp['VALUE'] = [];

                    while ($arPropItem = $rsPropItems->GetNext(true, false)) {
                        if ($arProp['MULTIPLE'] === 'Y')
                            $arProp['VALUE'][] = $arPropItem;
                        else
                            $arProp['VALUE'] = $arPropItem;
                    }
                    break;
            }
        }
        unset($arProp);
    }

    public function initPageNav()
    {
        CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        return CDBResult::GetNavParams($this->getNavParams());
    }

    public function getNavParams()
    {
        if ($this->arParams['COUNT'] === 0)
            return false;

        if (!empty($this->arParams['PAGER_TEMPLATE']))
            return ['nPageSize' => $this->arParams['COUNT']];

        return ['nTopCount' => $this->arParams['COUNT']];
    }
}
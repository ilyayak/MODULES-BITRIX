<?

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass('only:elements.list');

class OnlyElementsDetailComponent extends \OnlyElementsListComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);

        $arParams['COUNT'] = 1;
        $arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
        $arParams['ELEMENT_CODE'] = trim($arParams['ELEMENT_CODE']);

        $arParams['PAGER_TEMPLATE'] = false;
        $arParams['SET_META_TAGS'] = $this->checkParamsBoolVal($arParams['SET_META_TAGS'], 'Y');

        return $arParams;
    }

    /**
     * @return int
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        if ($this->arParams['ELEMENT_ID'] <= 0 && empty($this->arParams['ELEMENT_CODE'])) {
            ShowError('Не передан индификатор элемента ELEMENT_ID или ELEMENT_CODE');
            return 0;
        }

        if ($this->StartResultCache()) {
            if (!Loader::includeModule('iblock')) {
                $this->abortResultCache();
                ShowError('Не установлен модуль iblock');
                return 0;
            }

            $arIBlock = \CIBlock::GetByID($this->arParams['IBLOCK_ID'])->Fetch();

            if (!$arIBlock) {
                $this->abortResultCache();
                ShowError('Инфоблок не найден');
                return 0;
            }

            $this->arResult = reset($this->getItems());

            if (!$this->arResult) {
                $this->abortResultCache();
                return 0;
            }

            $this->arResult['IBLOCK'] = $arIBlock;

            $this->includeComponentTemplate();
        }

        if ($this->arParams['SET_META_TAGS'] === 'Y')
            $this->setMetaTags($this->arResult);

        $this->addAdminButtons();

        return intval($this->arResult['ID']);
    }

    public function getFilter()
    {
        $arFilter = parent::getFilter();

        if ($this->arParams['ELEMENT_ID'] > 0)
            $arFilter['ID'] = $this->arParams['ELEMENT_ID'];
        else
            $arFilter['=CODE'] = $this->arParams['ELEMENT_CODE'];

        return $arFilter;
    }

    public function setMetaTags($arItem)
    {
        global $APPLICATION;

        if (!empty($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])) {
            $APPLICATION->AddChainItem($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'], $arItem['DETAIL_PAGE_URL']);
            $APPLICATION->SetTitle($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']);
        } else {
            $APPLICATION->AddChainItem($arItem['NAME'], $arItem['DETAIL_PAGE_URL']);
            $APPLICATION->SetTitle($arItem['NAME']);
        }

        if (empty($arItem['IPROPERTY_VALUES']['ELEMENT_META_TITLE']))
            $APPLICATION->SetPageProperty('TITLE', $arItem['NAME']);

        foreach (['TITLE', 'KEYWORDS', 'DESCRIPTION'] as $sMetaTag) {
            if (!empty($arItem['IPROPERTY_VALUES']['ELEMENT_META_' . $sMetaTag]))
                $APPLICATION->SetPageProperty(strtolower($sMetaTag), $arItem['IPROPERTY_VALUES']['ELEMENT_META_' . $sMetaTag]);
        }
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
                $this->arResult['IBLOCK_ID'],
                $this->arResult['ID'],
                0,
                array('SECTION_BUTTONS' => false)
            );

            $this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
        }
    }
}
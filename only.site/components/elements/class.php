<?
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class OnlyElementsComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

        $arParams['SET_STATUS_404'] = $this->checkParamsBoolVal($arParams['SET_STATUS_404'], 'Y');
        $arParams['SHOW_404'] = $this->checkParamsBoolVal($arParams['SHOW_404'], 'Y');

        $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
        if ($arParams['CACHE_TIME'] <= 0)
            $arParams['CACHE_TIME'] = 604800;

        return $arParams;
    }

    private function checkParamsBoolVal($sValue, $sDefault = 'N')
    {
        $sValue = trim($sValue);
        if (empty($sValue) || !in_array($sValue, ['N', 'Y']))
            $sValue = $sDefault;

        return $sValue;
    }

    public function executeComponent()
    {
        if ($this->arParams['SEF_MODE'] !== 'Y') {
            ShowError('Компонент работает только в режиме ЧПУ!');
            return;
        }

        if (empty($this->arParams['SEF_FOLDER'])) {
            ShowError('Не передан обязатльный параметр \'SEF_FOLDER\'');
            return;
        }

        $arComponentVariables = array(
            'SECTION_ID',
            'SECTION_CODE',
            'ELEMENT_ID',
            'ELEMENT_CODE',
        );

        $arDefaultUrlTemplates = array(
            'main' => '',
            'section' => '#SECTION_CODE_PATH#/',
            'detail' => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/'
        );

        $arVariables = array();

        $arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates, $this->arParams['SEF_URL_TEMPLATES']);

        $obComponentEngine = new CComponentEngine($this);
        if (Loader::includeModule('iblock')) {
            $obComponentEngine->addGreedyPart('#SECTION_CODE_PATH#');
            $obComponentEngine->setResolveCallback(array('CIBlockFindTools', 'resolveComponentEngine'));
        }

        $sComponentPage = $obComponentEngine->guessComponentPath($this->arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);

        $sComponentPage = $this->checkComponentPage($sComponentPage);

        CComponentEngine::initComponentVariables($sComponentPage, $arComponentVariables, array(), $arVariables);

        $this->arResult = array(
            'FOLDER' => $this->arParams['SEF_FOLDER'],
            'URL_TEMPLATES' => $arUrlTemplates,
            'VARIABLES' => $arVariables
        );

        $this->includeComponentTemplate($sComponentPage);
    }

    private function checkComponentPage($sComponentPage)
    {
        global $APPLICATION;

        $b404 = false;

        if (!$sComponentPage) {
            $sComponentPage = 'main';
            $b404 = true;
        }

        if ($b404 && Loader::includeModule('iblock')) {
            $sFolder404 = str_replace('\\', '/', $this->arParams['SEF_FOLDER']);

            if ($sFolder404 != '/')
                $sFolder404 = '/' . trim(trim($sFolder404), '/') . '/';

            if (substr($sFolder404, -1) == '/')
                $sFolder404 .= 'index.php';

            if ($sFolder404 != $APPLICATION->GetCurPage(true)) {
                \Bitrix\Iblock\Component\Tools::process404(
                    '',
                    $this->arParams['SET_STATUS_404'] === 'Y',
                    $this->arParams['SET_STATUS_404'] === 'Y',
                    $this->arParams['SHOW_404'] === 'Y'
                );
            }
        }

        return $sComponentPage;
    }
}
<?
IncludeModuleLangFile(__FILE__);
Class abudagov_lastmodified extends CModule
{
    var $MODULE_ID = 'abudagov.lastmodified';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $strError = '';

    function __construct()
    {
        global $USER;
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("abudagov.lastmodified_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("abudagov.lastmodified_MODULE_DESC");
        $this->PARTNER_NAME = GetMessage("abudagov.lastmodified_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("abudagov.lastmodified_PARTNER_URI");

    }

    function InstallDB($arParams = array())
    {

        if (\Bitrix\Main\Loader::includeModule("highloadblock")) {
            $obHl = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
                'filter' => array('NAME' => 'PagesHash'),
                'select' => array('ID')
            ));
            if (!$arHl = $obHl->fetch()) {
                $arFields = array(
                    "NAME" => "PagesHash",
                    "TABLE_NAME" => "pages_hash"
                );
                $result = \Bitrix\Highloadblock\HighloadBlockTable::add($arFields);

                // Fields
                if ($result->getId()) {
                    $userTypeEntity = new CUserTypeEntity();
                    $arTypes = array("HASH_PAGE" => 'string', "HASH_URL" => 'string', "DATE_MODIFY" => 'datetime');
                    foreach ($arTypes as $code => $type) {
                        $userTypeData = array(
                            "ENTITY_ID" => "HLBLOCK_" . $result->getId(),
                            "FIELD_NAME" => "UF_" . $code,
                            "USER_TYPE_ID" => "$type",
                            "XML_ID" => "XML_ID_" . $code,
                            "SORT" => 100,
                            "MULTIPLE" => "N",
                            "MANDATORY" => "N",
                            "SHOW_FILTER" => "N",
                            "SHOW_IN_LIST" => "",
                            "EDIT_IN_LIST" => "",
                            "IS_SEARCHABLE" => "N",
                            "SETTINGS" => array("DEFAULT_VALUE" => "", "SIZE" => "20", "ROWS" => "1", "MIN_LENGTH" => "0", "MAX_LENGTH" => "0", "REGEXP" => "",),
                            "EDIT_FORM_LABEL" => array("ru" => "", "en" => "",),
                            "LIST_COLUMN_LABEL" => array("ru" => "", "en" => "",),
                            "LIST_FILTER_LABEL" => array("ru" => "", "en" => "",),
                            "ERROR_MESSAGE" => array("ru" => "", "en" => "",),
                            "HELP_MESSAGE" => array("ru" => "", "en" => "",),
                        );
                        $userTypeEntity->Add($userTypeData);
                    }
                    COption::SetOptionInt('abudagov.lastmodified', 'HLBlockId', $result->getId());
                }
            } else {
                COption::SetOptionInt('abudagov.lastmodified', 'HLBlockId', $arHl['ID']);
            }
        } else {
            echo 'Error: '.GetMessage('abudagov.lastmodified_need-highloadblock');
            exit;
        }

        return true;
    }

    function UnInstallDB($arParams = array())
    {
        // Удаляем хайлоадблок
        if (\Bitrix\Main\Loader::includeModule("highloadblock")) {
            if ($HLBlockId = COption::GetOptionInt('abudagov.lastmodified', 'HLBlockId')) {
                $obHl = \Bitrix\Highloadblock\HighloadBlockTable::getList(array(
                    'filter' => array('ID' => $HLBlockId),
                    'select' => array('ID')
                ));
                if ($arHl = $obHl->fetch()) {
                    \Bitrix\Highloadblock\HighloadBlockTable::delete($HLBlockId);
                    COption::SetOptionInt('abudagov.lastmodified', 'HLBlockId', false);
                }
            }
        }


        return true;
    }

    function InstallEvents()
    {
        RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "CAbudagovLastModified", "setLastModified");

        return true;
    }

    function UnInstallEvents()
    {
        UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "CAbudagovLastModified", "setLastModified");

        return true;
    }

    function InstallFiles($arParams = array())
    {

        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        if ($this->InstallDB()) {
            RegisterModule($this->MODULE_ID);
            $this->InstallFiles();
            $this->InstallEvents();
        }
    }

    function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallEvents();
        $this->UnInstallDB();
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }
}
?>

<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
Loc::loadMessages(__FILE__);

class newmark_speedup extends CModule{
    public function __construct(){
        if(file_exists(__DIR__."/version.php")){ //descriptions
            $arModuleVersion = array();
            include_once(__DIR__."/version.php");
            $this->MODULE_ID 		   = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION 	   = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME 		   = Loc::getMessage("NEWMARK_SPEEDUP_NAME");
            $this->MODULE_DESCRIPTION  = Loc::getMessage("NEWMARK_SPEEDUP_DESC");
            $this->PARTNER_NAME 	   = Loc::getMessage("NEWMARK_SPEEDUP_PARTNER_NAME");
            $this->PARTNER_URI  	   = Loc::getMessage("NEWMARK_SPEEDUP_PARTNER_URI");
        }

        return false;
    }

    public function DoInstall(){

        global $APPLICATION;

        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){
            $this->InstallFiles();
            $this->InstallDB();
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
        }else{
            $APPLICATION->ThrowException(
                Loc::getMessage("NEWMARK_SPEEDUP_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("NEWMARK_SPEEDUP_INSTALL_TITLE")." \"".Loc::getMessage("NEWMARK_SPEEDUP_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
    }


    public function DoUninstall(){

        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("NEWMARK_SPEEDUP_UNINSTALL_TITLE")." \"".Loc::getMessage("NEWMARK_SPEEDUP_NAME")."\"",
            __DIR__."/unstep.php"
        );
        return false;
    }

    //FILES
    public function InstallFiles(){
        CopyDirFiles(
            __DIR__."/assets/scripts",
            Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID."/",
            true,
            true
        );
        CopyDirFiles(
            __DIR__."/assets/styles",
            Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID."/",
            true,
            true
        );
        CopyDirFiles(
            __DIR__."/assets/images",
            Application::getDocumentRoot()."/bitrix/images/".$this->MODULE_ID."/",
            true,
            true
        );

        return false;
    }
    public function UnInstallFiles(){

        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/js/".$this->MODULE_ID
        );

        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/css/".$this->MODULE_ID
        );

        Directory::deleteDirectory(
            Application::getDocumentRoot()."/bitrix/images/".$this->MODULE_ID
        );

        return false;
    }

    //DB
    public function InstallDB(){
        return false;
    }
    public function UnInstallDB(){
        Option::delete($this->MODULE_ID);
        return false;
    }

    //EVENTS
    public function InstallEvents(){
        RegisterModuleDependences("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "Newmark\Speedup\Main", "speedAddScripts");
        RegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "Newmark\Speedup\Main", "speedActions");
        return false;
    }
    public function UnInstallEvents(){
        UnRegisterModuleDependences("main", "OnBeforeEndBufferContent", $this->MODULE_ID, "Newmark\Speedup\Main", "speedAddScripts");
        UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "Newmark\Speedup\Main", "speedActions");
        return false;
    }
}


<?

class only_site extends CModule
{
    const MODULE_ID = 'only.site';

    public $MODULE_ID = 'only.site',
        $MODULE_VERSION,
        $MODULE_VERSION_DATE,
        $MODULE_NAME = 'Хелперы для сайта',
        $PARTNER_NAME = 'Only',
        $PARTNER_URI = 'http://only.com.ru';

    public function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . 'version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    function InstallFiles()
    {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/only.site/components/", $_SERVER["DOCUMENT_ROOT"]."/local/components/only/", true, true);

        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $this->InstallFiles();
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
    }
}
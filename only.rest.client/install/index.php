<?

class only_rest_client extends CModule
{
    const MODULE_ID = 'only.rest.client';

    public $MODULE_ID = self::MODULE_ID,
        $MODULE_VERSION,
        $MODULE_VERSION_DATE,
        $MODULE_NAME = 'Клиент REST API',
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
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".MODULE_ID."/components/", $_SERVER["DOCUMENT_ROOT"]."/local/components/only/", true, true);
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
<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

Class akticomwebservices extends \CModule
{
    var $MODULE_ID = "akticomwebservices";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function akticomwebservices()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];     
        }

        $this->MODULE_NAME = "Akticom loger";
        $this->PARTNER_URI = "https://akticom.ru";
        $this->MODULE_DESCRIPTION = "После установки вы сможете как отпработал тот или иной веб сервис и посмотреть в случае чего лог ошибок";
    }

    function InstallFiles()
    {
        if(!is_dir($_SERVER["DOCUMENT_ROOT"] . "/local")){
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/akticomwebservices/install/web", $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, true);
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/akticomwebservices/install/menu/akticom_module_.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/akticom_module_.php", true, true);
        }
        else{
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/akticomwebservices/install/web", $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, true);    
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/akticomwebservices/install/menu/akticom_module_.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/akticom_module_.php", true, true);
        }
        return true;
    }

    function UnInstallFiles()
    {
        // DeleteDirFilesEx("/bitrix/web-class-log");
        DeleteDirFilesEx("/bitrix/admin/akticom_module_.php");
        DeleteDirFilesEx("/bitrix/web-price");
        DeleteDirFilesEx("/bitrix/web-service");
        DeleteDirFilesEx("/bitrix/web-stocks");        
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        RegisterModule("akticomwebservices");
        $APPLICATION->IncludeAdminFile("Установка модуля akticomwebservices", __DIR__ . "/step.php");
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        UnRegisterModule("akticomwebservices");
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля akticomwebservices",  __DIR__ . "/unstep.php");
    }
}
?>
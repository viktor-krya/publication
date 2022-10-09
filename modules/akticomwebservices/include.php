<?
/** @global \CMain $APPLICATION */
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Sale,
	Bitrix\Catalog;
    Loc::loadMessages(__FILE__);
    CModule::AddAutoloadClasses(
       "akticomwebservices",
       array(
        'WebServiceLog\\Aktikom_Class_log' => 'classes/class.php',
       )
    );
// require_once( $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/autoload.php');

    // require_once(__DIR__."/autoload.php");
    // CModule::IncludeModule("main");
    // Bitrix\Main\Loader::registerAutoloadClasses(
    // "Akticom_Web_Services",
    // array(
    //     "Akticom_Web_Services\\web\\Akticom_Web_log" =>"web/class.php",
    //     //...
	// 	)
	// );
    // CModule::AddAutoloadClasses(
    //     'Akticom_Web_Services',
    //     array(
    //         "Akticom_Web_log" =>"classes/class.php",
    //         )
    // );
?>
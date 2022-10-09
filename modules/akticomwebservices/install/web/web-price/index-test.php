<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("akticomwebservices");


$array = \Bitrix\Main\Web\Json::decode(file_get_contents('php://input'));
WebServiceLog\Aktikom_Class_log::test_data_check('price', $array);

echo WebServiceLog\Aktikom_Class_log::prr($array);
?>
<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// define("NO_KEEP_STATISTIC", true);
// define("NOT_CHECK_PERMISSIONS",true);
// define('BX_NO_ACCELERATOR_RESET', true);
// define('CHK_EVENT', true);
// define('BX_WITH_ON_AFTER_EPILOG', true);
ini_set('max_execution_time', 36000);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule("akticomwebservices");

WebServiceLog\Aktikom_Class_log::add_check("stocks", "Web сервис начал свою работу свою работу");

$IBLOCK_ID = "19";


$error = false;
$errorcheck = false;


//echo file_get_contents('php://input');
\Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "/web-stocks.txt");
try {
    $array = \Bitrix\Main\Web\Json::decode(file_get_contents('php://input'));
	if($array["hech"]){
		if(!web_service($array["hech"])){
			$error = true;
			$etap_error = 1;
			WebServiceLog\Aktikom_Class_log::error_check("stocks", "Не верно передаваемый хэш ключ");
			$errorcheck = true;
		}
	}
	else{
		$error = true;
		$etap_error = 2;
		WebServiceLog\Aktikom_Class_log::error_check("stocks", "Хэш не передается");
		$errorcheck = true;

	}
} catch (Exception $e) {
    $error = true;
	$etap_error = 3;
	WebServiceLog\Aktikom_Class_log::error_check("stocks", "Конеретная ошибка в обработке тела запроса");
	$errorcheck = true;
}     

if(!$error) {
	// \Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "/web-stocks.txt");
	WebServiceLog\Aktikom_Class_log::test_data_check('stocks', $array['stocks']);

	foreach($array["stocks"] as $key => $val) {
		$tovar_id = 0;
		WebServiceLog\Aktikom_Class_log::check_array('stocks', $key, $val);

		$arSelect = Array("ID", "IBLOCK_ID", "NAME");
		$arFilter1 = Array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID" => $val["XML_ID"]);
		$res = CIBlockElement::GetList(Array("created"=>"ASC"), $arFilter1, false, false, $arSelect);
		while($ob = $res->GetNextElement()){
			$arFields = $ob->GetFields();
			$tovar_id = $arFields["ID"];
		}		
		if ($tovar_id > 0) { //обновляем существующий товар
			// Получим записи из таблицы остатков товара для склада с ID=1 
			$rs = CCatalogStoreProduct::GetList(false, array('PRODUCT_ID'=> $tovar_id, 'STORE_ID' => 1)); 
			if($ar_fields = $rs->GetNext()) { 
                $arFields = Array(
                "PRODUCT_ID" => $ar_fields["PRODUCT_ID"],
                "STORE_ID" => 1,
                "AMOUNT" => $val["STOCK"]
    	        );
				if(CCatalogStoreProduct::Update($ar_fields['ID'], $arFields)) {
					$result_array["stocks"][$val["XML_ID"]] = "200";
				} else {
					$result_array["stocks"][$val["XML_ID"]] = "401";
					WebServiceLog\Aktikom_Class_log::error_check('stocks', 'Не получилось изменить количество товаров на первом складе для товара XML_ID = ' . $val["XML_ID"]);
					$errorcheck = true;
				}
			} else {    
				$arFields = Array(
					"PRODUCT_ID" => $tovar_id,
					"STORE_ID" => 1,
					"AMOUNT" => $val["STOCK"]
				);

				$ID = CCatalogStoreProduct::Add($arFields);
				if ($ID > 0) {
					$result_array["stocks"][$val["XML_ID"]] = "200";
				} else {
					$result_array["stocks"][$val["XML_ID"]] = "401";
					WebServiceLog\Aktikom_Class_log::error_check('stocks', 'Не получилось создать количественный учет первом складе для товара XML_ID = ' . $val["XML_ID"]);
					$errorcheck = true;
				}

			}
			// Получим записи из таблицы остатков товара для склада с ID=2 
			$rs = CCatalogStoreProduct::GetList(false, array('PRODUCT_ID'=> $tovar_id, 'STORE_ID' => 2));
			if($ar_fields = $rs->GetNext()) {
                $arFields = Array(
                "PRODUCT_ID" => $ar_fields["PRODUCT_ID"],
                "STORE_ID" => 2,
                "AMOUNT" => $val["STOCK_PROVIDER"]
    	        );
				if(CCatalogStoreProduct::Update($ar_fields['ID'], $arFields)) {
					$result_array["stocks"][$val["XML_ID"]] = "200";
				} else {
					$result_array["stocks"][$val["XML_ID"]] = "401";
					WebServiceLog\Aktikom_Class_log::error_check('stocks', 'Не получилось изменить количество товаров на втором складе для товара XML_ID = ' . $val["XML_ID"]);
					$errorcheck = true;
				}
			} else {
				$arFields = Array(
					"PRODUCT_ID" => $tovar_id,
					"STORE_ID" => 2,
					"AMOUNT" => $val["STOCK_PROVIDER"]
				);
    
				$ID = CCatalogStoreProduct::Add($arFields);
				if ($ID > 0) {
					$result_array["stocks"][$val["XML_ID"]] = "200";
				} else {
					$result_array["stocks"][$val["XML_ID"]] = "401";
					WebServiceLog\Aktikom_Class_log::error_check('stocks', 'Не получилось создать количественный учет втором складе для товара XML_ID = ' . $val["XML_ID"]);
					$errorcheck = true;
				}
			}
		} else {
			$result_array["stocks"][$val["XML_ID"]] = "401";
		}
	}
	if(!$errorcheck)
		WebServiceLog\Aktikom_Class_log::add_check("stocks", "Web сервис закончил свою работу");
	else{
		WebServiceLog\Aktikom_Class_log::add_check("stocks", "Web сервис закончил свою работу c ошибками");
	}
	echo json_encode ($result_array);
} else {

   
	WebServiceLog\Aktikom_Class_log::error_check("stocks", "ERROR json.");
    echo "ERROR json.";
	$errorcheck = true;
	if(!$errorcheck)
		WebServiceLog\Aktikom_Class_log::add_check("stocks", "Web сервис закончил свою работу");
	else{
		WebServiceLog\Aktikom_Class_log::add_check("stocks", "Web сервис закончил свою работу c ошибками");
	}
}

?>
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

$IBLOCK_ID = "35";


$error = false;


//echo file_get_contents('php://input');

// try {
    $array = \Bitrix\Main\Web\Json::decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/test/web-stocks.json"));
// } catch (Exception $e) {
//     $error = true;
// }    

if(!$error) {
	\Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "web-stocks.txt");

	foreach($array["stocks"] as $key => $val) {
		$tovar_id = 0;
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
				}
			}
		} else {
			$result_array["stocks"][$val["XML_ID"]] = "401";
		}
	}
	echo json_encode ($result_array);
} else {

    echo "ERROR json.";

}

?>
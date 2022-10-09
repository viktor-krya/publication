<?
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// define("NO_KEEP_STATISTIC", true);
// define("NOT_CHECK_PERMISSIONS",true);
// define('BX_NO_ACCELERATOR_RESET', true);
// define('CHK_EVENT', true);
// define('BX_WITH_ON_AFTER_EPILOG', true);
// ini_set('max_execution_time', 460000000);
// ini_set('max_input_vars', 300000000);
// ini_set("memory_limit", 2048M);
echo ini_get("max_execution_time") . "\n" ;
echo ini_get("max_input_time") . "\n" ;
echo ini_get("upload_max_filesize") . "\n" ;
echo ini_get("post_max_size") . "\n" ;

// require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule("akticomwebservices");

$IBLOCK_ID = "19";


$error = false;


//echo file_get_contents('php://input');

try {
    $array = \Bitrix\Main\Web\Json::decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/bitrix/web-price/price.json")); //$_SERVER["DOCUMENT_ROOT"] . "/test/test.json"
    
} catch (Exception $e) {
    $error = true;
}    

if(!$error) {
	// \Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "web-price.txt");

	foreach($array["prices"] as $key => $val) {
		WebServiceLog\Aktikom_Class_log::check_array('price', $key, $val);
		
		$tovar_id = 0;
		$arSelect = Array("ID", "IBLOCK_ID", "NAME");
		$arFilter1 = Array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID" => $val["XML_ID"]);
		$res = CIBlockElement::GetList(Array("created"=>"ASC"), $arFilter1, false, false, $arSelect);
		while($ob = $res->GetNextElement()){
			$arFields = $ob->GetFields();
			$tovar_id = $arFields["ID"];
		}		
		if ($tovar_id > 0) { //обновляем существующий товар
			$obPrice = new CPrice;
            // обновляем цены (базовая)
            $arFieldsForPrice = [
                "PRODUCT_ID"       => $tovar_id, 
                "CATALOG_GROUP_ID" => 1,
                "PRICE"            => $val["PRICE"],
                "CURRENCY"         => "RUB",
            ];
            $resPrice = $obPrice->GetList([], ["PRODUCT_ID" => $tovar_id, "CATALOG_GROUP_ID" => 1]);
            if ($arPrice = $resPrice->Fetch()) {
                if($obPrice->Update($arPrice["ID"], $arFieldsForPrice)) {
					$result_array["prices"][$val["XML_ID"]] = "200";
				} else {
					$result_array["prices"][$val["XML_ID"]] = "401";
				}
            } else {
                if($obPrice->Add($arFieldsForPrice)) {
					$result_array["prices"][$val["XML_ID"]] = "200";
				} else {
					$result_array["prices"][$val["XML_ID"]] = "401";
				}
            }

			if($result_array["prices"][$val["XML_ID"]] == "401"){
				WebServiceLog\Aktikom_Class_log::error_check("price", "У товара с XML_ID = " . $val["XML_ID"] . " не обновилась основная цена");
			}
            // обновляем цены (со скидкой)
            $arFieldsForPrice = [
                "PRODUCT_ID"       => $tovar_id, 
                "CATALOG_GROUP_ID" => 2,
                "PRICE"            => $val["PRICE_SALE_OUT"],
                "CURRENCY"         => "RUB",
            ];
            $resPrice = $obPrice->GetList([], ["PRODUCT_ID" => $tovar_id, "CATALOG_GROUP_ID" => 2]);
            if ($arPrice = $resPrice->Fetch()) {
				if($val["PRICE_SALE_OUT"] == "0.00" || $val["PRICE_SALE_OUT"] == "0" || $val["PRICE_SALE_OUT"] == ""){
					
					if($obPrice->Delete($arPrice["ID"])) {
						$result_array["prices"][$val["XML_ID"]] = "200_delete";
					} else {
						$result_array["prices"][$val["XML_ID"]] = "401_delete";
					}
				}
				else{
					echo "<br> ------ <br>";
					if($obPrice->Update($arPrice["ID"], $arFieldsForPrice)) {
						$result_array["prices"][$val["XML_ID"]] = "200_update";
					} else {
						$result_array["prices"][$val["XML_ID"]] = "401_update";
					}
				}
            } else {
                if($obPrice->Add($arFieldsForPrice)) {
					$result_array["prices"][$val["XML_ID"]] = "200_Add";
				} else {
					$result_array["prices"][$val["XML_ID"]] = "401_Add";
				}
            }
			if($result_array["prices"][$val["XML_ID"]] != "200"){
				WebServiceLog\Aktikom_Class_log::error_check("price", "У товара с XML_ID = " . $val["XML_ID"] . " не обновилась цена со скидкой");
			}
			
		} else {
			$result_array["prices"][$val["XML_ID"]] = "401";
			WebServiceLog\Aktikom_Class_log::error_check("price", "Даннного товара (XML_ID = "  . $val["XML_ID"] . ") не существует, не возможно обновить цену");
			$errorcheck = true;
		}
	}
    header('Content-Type: application/json');
	echo "result";
	echo json_encode ($result_array);
} else {

	if(!$error){

	}
	else{
    	echo "ERROR json.";
	}
}

?>
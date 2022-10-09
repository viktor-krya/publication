<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);
define('BX_WITH_ON_AFTER_EPILOG', true);
ini_set('max_execution_time', 36000);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');

$IBLOCK_ID = "35";
$measure_array = array();
$res_measure = CCatalogMeasure::getList();
while($measure = $res_measure->Fetch()) { 
	$measure_array[$measure["SYMBOL_RUS"]] = $measure["ID"];
} 

$error = true;

//CIBlockProperty::Delete(11824);

/*$arFields__ = Array(
  "NAME" => "Код",
  "ACTIVE" => "Y",
  "SORT" => "600",
  "CODE" => "CUSTOM_CODE",
  "PROPERTY_TYPE" => "S",
  "IBLOCK_ID" => $IBLOCK_ID,
  "WITH_DESCRIPTION" => "N",
  );
$ibp = new CIBlockProperty;
$PropID = $ibp->Add($arFields__);*/


//echo file_get_contents('php://input');

// try {
//     $array = \Bitrix\Main\Web\Json::decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/test/web-service.json"));
// 	// $arFild = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/test/web-service.json");
// 	// $array = json_decode($arFild, true);
// 	// prr($array);
// } catch (Exception $e) {
//     $error = true;
// }    
if($array["hech"]){
	if(!web_service($array["hech"])){
		$error = true;
	}
}
else{
	$error = true;
}
if(!$error) {
	// \Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "web-servese.txt");
	
	$result_array = array();
	$cat_array = array();
	foreach($array["categories"] as $key => $val) {
		$section_guid = $val["XML_ID"];
		$parent_cat = $val["PARENT_CAT"];

		$cat_array[$section_guid]["GUID"] = $section_guid;
		$cat_array[$section_guid]["NAME"] = $val["NAME"];
		$cat_array[$section_guid]["PARENT_CAT"] = $val["PARENT_CAT"];
	}

	if (count($cat_array) > 0) {
		//прогоняем все разделы по базе - если есть, обновляем, если нет - создаем в корне неактивными (потом вторым шагом по ним пройдемся, активируем и раскидаем по родителям)

		foreach($cat_array as $key => $val) {
			$section_fields = array();
			$arFilter = Array('IBLOCK_ID'=>$IBLOCK_ID, 'XML_ID'=>$key);
			$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);
			while($ar_result = $db_list->GetNext()) {
				$section_fields = $ar_result;
			}

			if($section_fields["ID"] > 0) {
				$bs = new CIBlockSection;
				$arFields = Array(
				  "IBLOCK_ID" => $IBLOCK_ID,
				  "NAME" => $val["NAME"]
				  );
				$bs->Update($section_fields["ID"], $arFields);
				$cat_array[$key]["ELEMENT_ID"] = $section_fields["ID"];
			} else {
				$bs = new CIBlockSection;
				$arFields = Array(
				  "ACTIVE" => "N",
				  "IBLOCK_ID" => $IBLOCK_ID,
				  "XML_ID" => $key,
				  "NAME" => $val["NAME"]
				  );
				$ID = $bs->Add($arFields);
				if ($ID > 0) {$cat_array[$key]["ELEMENT_ID"] = $ID;}
			}

		}


		//распределяем разделы по родителям
		foreach($cat_array as $key => $val) {
			if ($val["ELEMENT_ID"] > 0) {
				$parent_guid = $val["PARENT_CAT"];
				if (trim($parent_guid) != "") {
					$parent_id="";
					foreach($cat_array as $key_ => $val_) {
						if($parent_guid == $val_["GUID"]) { $parent_id= $val_["ELEMENT_ID"]; }
					}
					if ($parent_id > 0) { //Переносим в родителя и активируем
						$bs = new CIBlockSection;
						$arFields = Array(
						  "ACTIVE" => "Y",
						  "IBLOCK_SECTION_ID" => $parent_id,
						  "IBLOCK_ID" => $IBLOCK_ID
						);
						if($bs->Update($val["ELEMENT_ID"], $arFields)) { $result_array["categories"][$key] = "200"; }
					}
				} else { // Родителя нет - значит, активируем и делаем верхним уровнем
					$bs = new CIBlockSection;
					$arFields = Array("ACTIVE" => "Y", "IBLOCK_SECTION_ID" => false);
					if($bs->Update($val["ELEMENT_ID"], $arFields)) { $result_array["categories"][$key] = "200"; }
				}
			}
		}

		//если нет положительного статуса по обновлению категории - значит результат отрицательный
		foreach($cat_array as $key => $val) {
			if (!isset($result_array["categories"][$key])) { $result_array["categories"][$key] = "401"; }
		}
	}

	foreach($array["items"] as $key => $val) {
		$meas_id = "";
		$tovar_id = 0;
		$arSelect = Array("ID", "IBLOCK_ID", "NAME");
		$arFilter1 = Array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID" => $val["XML_ID"]);
		$res = CIBlockElement::GetList(Array("created"=>"ASC"), $arFilter1, false, false, $arSelect);
		while($ob = $res->GetNextElement()){
			$arFields = $ob->GetFields();
			$tovar_id = $arFields["ID"];
		}


		$arLoadProductArray = Array(
			"NAME" => $val["NAME"],
			"IBLOCK_ID" => $IBLOCK_ID
		);

		if ($val["ACTIVITY"]) {
			$arLoadProductArray["ACTIVE"] = "Y";
		} else {
			$arLoadProductArray["ACTIVE"] = "N";
		}

		if (trim($val["DETAIL_PICTURE"]) != "") {
			$imagee = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $val["DETAIL_PICTURE"]));
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/imgbs64.png',$imagee);
			$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/imgbs64.png");
		}
		if (trim($val["CATEGORY"]) != "") {
			$section_id = "";
			$arFilter_category = Array('IBLOCK_ID'=>$IBLOCK_ID, 'XML_ID'=>trim($val["CATEGORY"]));
			$db_list_category = CIBlockSection::GetList(Array($by=>$order), $arFilter_category, true);
			while($ar_result_category = $db_list_category->GetNext()) {
				$section_id = $ar_result_category["ID"];
			}				
			if ($section_id > 0) {
				$arLoadProductArray["IBLOCK_SECTION"] = $section_id;
			} else {
				$arLoadProductArray["IBLOCK_SECTION"] = false;
			}
		} else {
			$arLoadProductArray["IBLOCK_SECTION"] = false;
		}

		$PROP = array(
			"ARTICLE" => $val["ARTICUL"],
			"vysota" => $val["VYSOTA"], 
			"glubina" => $val["GLUBINA"],
			"shirina" => $val["SHIRINA"], 
			"ves" => $val["VES"],
			"ORGANIZATION" => $val["ORGANIZATION"],
			"SHTRIHCODE" => $val["SHTRIHCODE"],
			"CUSTOM_CODE" => $val["CODE"],
		);
		$analog_ids_array = array();
		if(count($val["ANALOG"]) > 0) {
			foreach($val["ANALOG"] as $k_ => $v_) {
				if (trim($v_) != "") {
					$arSelect_analog = Array("ID", "IBLOCK_ID");
					$arFilter_analog = Array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID" => $v_);
					$res_analog = CIBlockElement::GetList(Array("created"=>"ASC"), $arFilter_analog, false, false, $arSelect_analog);
					while($ob_analog = $res_analog->GetNextElement()){
						$arFields_analog = $ob_analog->GetFields();
						$analog_ids_array[$arFields_analog["ID"]] = $arFields_analog["ID"];
					}
				}
			}
		}

		if (count($analog_ids_array) > 0) {
			$PROP["ANALOG"] = $analog_ids_array;
		} else {
			$PROP["ANALOG"] = false;
		}


		$brand = 0;

		if (trim($val["BRAND"]) != "") {
			$arSelect_brand = Array("ID", "IBLOCK_ID", "NAME");
			$arFilter_brand = Array("IBLOCK_ID"=>2, "NAME" => trim($val["BRAND"]));
			$res_brand = CIBlockElement::GetList(Array("created"=>"ASC"), $arFilter_brand, false, false, $arSelect_brand);
			while($ob_brand = $res_brand->GetNextElement()){
				$arFields_brand = $ob_brand->GetFields();
				$brand = $arFields_brand["ID"];
			}


			if ($brand > 0) {
				$PROP["BRAND"] = $brand;
				$PROP["VENDOR_NEW"] = trim($val["BRAND"]);
			} else {
				$el_brand = new CIBlockElement;

				$arParams_brand = array("replace_space"=>"-","replace_other"=>"-");
				$brand_code = Cutil::translit(trim($val["BRAND"]),"ru", $arParams_brand);


				$arLoadProductArray_brand = Array(
					"NAME" => trim($val["BRAND"]),
					"IBLOCK_ID" => 2,
					"CODE" => $brand_code
				);

				if($BRAND_ID = $el_brand->Add($arLoadProductArray_brand)) {
					$PROP["BRAND"] = $BRAND_ID;
					$PROP["VENDOR_NEW"] = trim($val[BRAND]);
				} else {
					//echo "Error: ".$el_brand->LAST_ERROR;
					$PROP["BRAND"] = false;
				}
							
			}

		}

		if(isset($measure_array[$val["MEASURE"]])) {
			$meas_id = $measure_array[$val["MEASURE"]];
		} 

		if ($tovar_id > 0) { //обновляем существующий товар
			$el = new CIBlockElement;

			CIBlockElement::SetPropertyValuesEx($tovar_id, false, $PROP);

			$PRODUCT_ID = $tovar_id;
			if($el->Update($PRODUCT_ID, $arLoadProductArray)) {
				$result_array["items"][$val["XML_ID"]] = "200";
			} else {
				$result_array["items"][$val["XML_ID"]] = "401";
			}

			$ar_res_tovar = CCatalogProduct::GetByID($tovar_id);
			
			$arFields_product_prors = array("ID"=> $tovar_id, 'WIDTH' => $val["SHIRINA"], 'LENGTH' => $val["GLUBINA"], 'HEIGHT' => $val["VYSOTA"], 'WEIGHT' => $val["VES"]);
			if ($meas_id > 0) { $arFields_product_prors["MEASURE"] = $meas_id; }
			if ($ar_res_tovar["ID"] > 0) {
				CCatalogProduct::Update($tovar_id, $arFields_product_prors);
			} else {
				CCatalogProduct::Add($arFields_product_prors);
			}

			$ratioId = 0;
			$curElementRatio = CCatalogMeasureRatio::getList(Array(),array('IBLOCK_ID' => $IBLOCK_ID, 'PRODUCT_ID' => $tovar_id),false, false);
			 while ($arRatio = $curElementRatio->GetNext()) {
				  $ratioId = $arRatio['ID'];
			 }

			if ($ratioId > 0) {
				CCatalogMeasureRatio::update($ratioId, Array('PRODUCT_ID' => $tovar_id, 'RATIO' => $val["RATIO"]));
			} else {
				$ratioId = CCatalogMeasureRatio::add(Array('PRODUCT_ID' => $tovar_id, 'RATIO' => $val["RATIO"]));
			}



		} else { //создаем новый товар
			$el = new CIBlockElement;
			$arLoadProductArray["XML_ID"] = $val["XML_ID"];

			$arParams_tovar = array("replace_space"=>"-","replace_other"=>"-");
			$tovar_code = Cutil::translit(trim($val["NAME"]),"ru", $arParams_tovar);

			$arLoadProductArray["CODE"] = $tovar_code;

			$arLoadProductArray["PROPERTY_VALUES"] = $PROP;

			if($PRODUCT_ID = $el->Add($arLoadProductArray)) {
				$ar_res_tovar = CCatalogProduct::GetByID($tovar_id);
				$arFields_product_prors = array("ID"=> $PRODUCT_ID, 'WIDTH' => $val["SHIRINA"], 'LENGTH' => $val["GLUBINA"], 'HEIGHT' => $val["VYSOTA"], 'WEIGHT' => $val["VES"]);
				if ($meas_id > 0) { $arFields_product_prors["MEASURE"] = $meas_id; }
				CCatalogProduct::Add($arFields_product_prors);

				$ratioId = CCatalogMeasureRatio::add(Array('PRODUCT_ID' => $PRODUCT_ID, 'RATIO' => $val["RATIO"]));
				$result_array["items"][$val["XML_ID"]] = "200";
			} else {
				$result_array["items"][$val["XML_ID"]] = "401";
			}
		
		}

	}
	echo json_encode ($result_array);
} else {

    echo "ERROR json.<br><br>";
}

?>
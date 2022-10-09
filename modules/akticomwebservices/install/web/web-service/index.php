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
use lib\FileLogs;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 
CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');
CModule::IncludeModule("akticomwebservices");


$loging = new FileLogs("service");





$IBLOCK_ID = "19";
$measure_array = array();
$res_measure = CCatalogMeasure::getList();
while($measure = $res_measure->Fetch()) { 
	$measure_array[$measure["SYMBOL_RUS"]] = $measure["ID"];
} 

$error = false;
$errorcheck = false;
WebServiceLog\Aktikom_Class_log::add_check("service", "Web сервис начал свою работу свою работу");
$loging->addToLog("Начало работы сервиса");


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

try {
    $array = \Bitrix\Main\Web\Json::decode(file_get_contents('php://input'));
	if($array["hech"]){
		if(!web_service($array["hech"])){
			$error = true;
			$etap_error = 1;
			WebServiceLog\Aktikom_Class_log::error_check("service", "Не верно передаваемый хэш ключ");
			$errorcheck = true;

			$loging->addToLog("Некоректный хэш ключ");
		}
	}
	else{
		$error = true;
		$etap_error = 2;
		WebServiceLog\Aktikom_Class_log::error_check("service", "Хэш не передается");
		$errorcheck = true;

		$loging->addToLog("Пустой хэш ключ");

	}
} catch (Exception $e) {
    $error = true;
	$etap_error = 3;
	WebServiceLog\Aktikom_Class_log::error_check("service", "Конеретная ошибка в обработке тела запроса");
	$errorcheck = true;
}    
// \Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "web-servese.txt");
if(!$error) {
	// \Bitrix\Main\Diag\Debug::writeToFile(array('$array' => $array), "", "web-servese.txt");
	
	$result_array = array();
	$cat_array = array();
	// WebServiceLog\Aktikom_Class_log::test_data_check('service', $array['categories']);


	/*
	foreach($array["categories"] as $key => $val) {

		WebServiceLog\Aktikom_Class_log::check_array('service', $key, $val);
		$section_guid = $val["XML_ID"];
		$parent_cat = $val["PARENT_CAT"];

		$cat_array[$section_guid]["GUID"] = $section_guid;
		$cat_array[$section_guid]["NAME"] = $val["NAME"];
		$cat_array[$section_guid]["PARENT_CAT"] = $val["PARENT_CAT"];

	}
	*/


	/*if (count($cat_array) > 0) {
		//прогоняем все разделы по базе - если есть, обновляем, если нет - создаем в корне неактивными (потом вторым шагом по ним пройдемся, активируем и раскидаем по родителям)

		foreach($cat_array as $key => $val) {
			
			$section_fields = array();

			$loging->addToLog("Обработка раздела с ГУИДом - ".$key);

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
				if(!$bs->Update($section_fields["ID"], $arFields)){
					WebServiceLog\Aktikom_Class_log::error_check("service", "Не получилось обновить раздел с ID = " . $section_fields["ID"]);

					$loging->addToLog("Ошибка при изменение раздела с ГУИДом - ".$key);

					$errorcheck = true;
				}
				$cat_array[$key]["ELEMENT_ID"] = $section_fields["ID"];
			} else {

				$loging->addToLog("Создание нового раздела с ГУИДом - ".$key);

				$bs = new CIBlockSection;
				$arFields = Array(
				  "ACTIVE" => "N",
				  "IBLOCK_ID" => $IBLOCK_ID,
				  "XML_ID" => $key,
				  "NAME" => $val["NAME"]
				  );
				if($ID = $bs->Add($arFields)){
					WebServiceLog\Aktikom_Class_log::error_check('service', "Не получилось добавить новый раздел с XML_ID = " . $arFields["XML_ID"]);
					$errorcheck = true;

					$loging->addToLog("Ошибка при Добавление раздела с ГУИДом - ".$key);
				}
				else{
					if ($ID > 0) {$cat_array[$key]["ELEMENT_ID"] = $ID;}
				}
			}

		}


		//распределяем разделы по родителям
		foreach($cat_array as $key => $val) {
			if ($val["ELEMENT_ID"] > 0) {
				$parent_guid = $val["PARENT_CAT"];
				if (trim($parent_guid) != "") {
					$parent_id="";
					foreach($cat_array as $key_ => $val_) {
						if($parent_guid == $val_["GUID"]) { 
							$parent_id= $val_["ELEMENT_ID"];
							$parent_guid = $val_["GUID"];
						}
					}
					if ($parent_id > 0) { //Переносим в родителя и активируем
						$bs = new CIBlockSection;
						$arFields = Array(
						  "ACTIVE" => "Y",
						  "IBLOCK_SECTION_ID" => $parent_id,
						  "IBLOCK_ID" => $IBLOCK_ID
						);
						if($bs->Update($val["ELEMENT_ID"], $arFields)) { 
							$result_array["categories"][$key] = "200";

							$loging->addToLog("Разедл ГУИД(.$key.) успешно привязан к родителю ГУИД(.$parent_guid.)");
						}
						else{
							WebServiceLog\Aktikom_Class_log::error_check("service", "Не получилось привязать раздел к его родителю ID = " . $val["ELEMNT_ID"]);$errorcheck = true;
							$loging->addToLog("Разедл ГУИД(.$key.) НЕ привязан к родителю ГУИД(.$parent_guid.)");
						}
					}
				} else { // Родителя нет - значит, активируем и делаем верхним уровнем
					$bs = new CIBlockSection;
					$arFields = Array("ACTIVE" => "Y", "IBLOCK_SECTION_ID" => false);
					if($bs->Update($val["ELEMENT_ID"], $arFields)) { 
						$result_array["categories"][$key] = "200";
						$loging->addToLog("Разедл ГУИД(.$key.) сделан родительским"); 
					}
					else{
						WebServiceLog\Aktikom_Class_log::error_check("service", "Не получилось активировать раздел и сделать его родительским ID = " . $val["ELEMNT_ID"]);$errorcheck = true;
						$loging->addToLog("Разедл ГУИД(.$key.) не удалось сделать родителем и активировать"); 
					}
				}
			}
			
		}

		//если нет положительного статуса по обновлению категории - значит результат отрицательный
		foreach($cat_array as $key => $val) {
			if (!isset($result_array["categories"][$key])) { $result_array["categories"][$key] = "401"; }
		}
	}*/
	
	foreach($array["items"] as $key => $val) {

		WebServiceLog\Aktikom_Class_log::check_array('service', $key, $val);
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
			/*
			$imagee = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $val["DETAIL_PICTURE"]));
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/imgbs64.png',$imagee);
			$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/imgbs64.png");
			*/
			$ar_img = explode('.',$val['DETAIL_PICTURE']);
			$local_file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/web-service/local.' . $ar_img[1];
			$server_file = str_replace('\\', '/', $val['DETAIL_PICTURE']);


			$handle = fopen($local_file, 'w');
			$ftp_server = "185.197.35.217";
			$ftp_user_name = "akticom";
			$ftp_user_pass = "x39g61eZ3";

			$ftp = ftp_connect($ftp_server, 21, 36000);
			if(!$login_result = ftp_login($ftp , $ftp_user_name, $ftp_user_pass)){
				exit("Не могу соединиться");
			}else{
				ftp_pasv($ftp, true);
				if (ftp_fget($ftp,$handle, $server_file, FTP_BINARY,0)) {
					echo "Произведена запись в $local_file\n";
				} else {
					echo "Не удалось завершить операцию\n";
				}
			}
			ftp_close($ftp);

			$filesize = filesize($local_file);
			if($filesize == 0){
				$loging->addToLog("Ошибка создания товара с ГУИДом - ".$val["XML_ID"]." пришло пустое изображение");
				continue;
			}
			$arLoadProductArray["DETAIL_PICTURE"] = CFile::MakeFileArray($local_file);
			
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
			"SUPPLIER_NAME" => $val['SUPPLIER_NAME']
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
					$PROP["VENDOR_NEW"] = trim($val["BRAND"]);
				} else {
					//echo "Error: ".$el_brand->LAST_ERROR;
					$PROP["BRAND"] = false;
				}
							
			}

		}

		if(isset($measure_array[$val["MEASURE"]])) {
			$meas_id = $measure_array[$val["MEASURE"]];
		} 
		$PROP = array_filter($PROP);
		
		if ($tovar_id > 0) { //обновляем существующий товар
			$el = new CIBlockElement;

			CIBlockElement::SetPropertyValuesEx($tovar_id, false, $PROP);

			$PRODUCT_ID = $tovar_id;
			if($el->Update($PRODUCT_ID, $arLoadProductArray)) {
				$result_array["items"][$val["XML_ID"]] = "200";
			} else {
				$result_array["items"][$val["XML_ID"]] = "401";
				WebServiceLog\Aktikom_Class_log::error_check('service', "не получилось обновить товара с ID  = " . $PRODUCT_ID . " и XML_ID = " . $val["XML_ID"] );
				$errorcheck = true;
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

			//Проверка пустых обязательных значений 
			$must_require = array(
				"Артикул" => !empty($PROP["ARTICLE"]),
				"Наименование" => !empty($val["NAME"]),
				"Фото" => !empty($val["DETAIL_PICTURE"]),
				"Кратность" => !empty($val["RATIO"]),
				"Единица измерения" => !empty($val["MEASURE"]),
				"Категория" => !empty($val["CATEGORY"]),
				"Активность" => !empty($val["ACTIVITY"])
			);
			$is_createble = true;
			$log_title = "";
			$log_text = "";
			foreach($must_require as $title => $value){
				if($value == false){
					$is_createble = false;
					$log_title .= $title."; ";
				}
			}
			if($is_createble == false){
				$loging->addToLog("Ошибка создания товара с ГУИДом - ".$val["XML_ID"]." нет поля ( ".$log_title." )");
				continue;
			}
			//Проверка входных данных
			/*
	
				Артикул,
				Наименование,
				Фото,
				Категория,
				Активность,
				Кратность,
				Единица измерения
	
			*/

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
				WebServiceLog\Aktikom_Class_log::error_check('service', "не получилось создать товара с XML_ID = " . $val["XML_ID"] );
				$errorcheck = true;
			}
		
		}

	}
	if(!$errorcheck)
		WebServiceLog\Aktikom_Class_log::add_check("service", "Web сервис закончил свою работу");
	else{
		WebServiceLog\Aktikom_Class_log::add_check("service", "Web сервис закончил свою работу c ошибками");
	}
	echo json_encode ($result_array);
} else {

   
	WebServiceLog\Aktikom_Class_log::error_check("service", "ERROR json.");
    echo "ERROR json.";
	$errorcheck = true;
	if(!$errorcheck)
		WebServiceLog\Aktikom_Class_log::add_check("service", "Web сервис закончил свою работу");
	else{
		WebServiceLog\Aktikom_Class_log::add_check("service", "Web сервис закончил свою работу c ошибками");
	}
}

?>
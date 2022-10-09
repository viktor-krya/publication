<? 
namespace WebServiceLog;

use webservice;

class Aktikom_Class_log {
    // основной функционал класса
    public static function prr($data){
        echo "<pre>" . print_r($data, 1) . "</pre>";
    }
    private static function create_log_file($web_text_log, $log_create, $data){
        // $ar = array($log_create, $web_text_log, $data);
        $arFileProp = array();
        switch($web_text_log){
            case "ok":
                $arFileProp["FILE_NAME"] = "log-ok.txt";
                $arFileProp["TYPE_MESSAGE"] = "Успешно";
            break;
            case "error":
                $arFileProp["FILE_NAME"] = "log-error.txt";
                $arFileProp["TYPE_MESSAGE"] = "Текст ошибки";
            break;
            case "error_element":
                $arFileProp["FILE_NAME"] = "log-error-element.txt";
                $arFileProp["TYPE_MESSAGE"] = "Ошибки в отправленных запросах";
            break;
            case "data_test":
                $arFileProp["FILE_NAME"] = "log-data-test.txt";
                $arFileProp["TYPE_MESSAGE"] = "Тестовый массив";
            break;
        }
        switch($log_create){
            case "price":
                $arFileProp["FOLDER_NAME"] = "price";
            break;
            case "service":
                $arFileProp["FOLDER_NAME"] = "service";
            break;
            case "stocks":
                $arFileProp["FOLDER_NAME"] = "stocks";
            break;
        }
        for($i = 0; $i < 2 ; $i++){
            $dir_all_log = $_SERVER["DOCUMENT_ROOT"] . (($i == 0) ? "/upload/log-web-service/" : "/upload/log-web-service/user/");
            $dir_this_log = $dir_all_log . $arFileProp["FOLDER_NAME"] . "/";
            $file_log_this = $dir_this_log . $arFileProp["FILE_NAME"];
            if(!is_dir($dir_all_log)){
                mkdir($dir_all_log, 0777, true);
            }
            if(!is_dir($dir_this_log)){
                mkdir($dir_this_log, 0777, true);
            }
            if(!file_exists($file_log_this)){
                $fop = fopen($file_log_this, "w+");
                if($i == 0)
                    fwrite($fop, "Дата^" . $arFileProp["TYPE_MESSAGE"] . "~");
            }
            else{
                $fop = fopen($file_log_this, "a+");
            }
            if($i == 0)
                fwrite($fop, $data["DATE"] . "^" . $data["VALUE"] . "~");
            else
                fwrite($fop, $data["DATE"] . " _<====>_ " . $data["VALUE"] . "\n");
            fclose($fop);
        }
    }

    // основной функционал класса

    // успешная обработка 

    public static function add_check($type_folder, $data){
        // require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
      //  CModule::IncludeModule("akticomwebservices");
        // $log_ok  = COption::GetOptionString("akticomwebservices", "log_ok");
        if($log_ok == "Y"){
            $arOk = array(
                "DATE" => date("d-m-Y H:i:s"),
                "VALUE" => $data
            );
            Aktikom_Class_log::create_log_file('ok', $type_folder, $arOk);
        }
    }

    // успешная обработка 
    // обработка с ошибкой

    public static function error_check($type_folder, $data){
        // require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
      //  CModule::IncludeModule("akticomwebservices");
        // $log_error  = COption::GetOptionString("akticomwebservices", "log_error");
        if($log_error == "Y") {
            $arError = array(
                "DATE" => date("d-m-Y H:i:s"),
                "VALUE" => $data
            );
            Aktikom_Class_log::create_log_file('error', $type_folder, $arError);
        }
        // Aktikom_Class_log::add_check("ok", $type_folder, "Web сервис закончил свою работу с ошибкой");
    }
    
    // обработка с ошибкой
    // логирование приходящего массива

    public static function test_data_check($type_folder, $data){
        // require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
      //  CModule::IncludeModule("akticomwebservices");
        // $log_data  = COption::GetOptionString("akticomwebservices", "log_data");
        if( $log_data == "y"){
            $arMass = array(
                "DATE" => date("d-m-Y H:i:s"),
                "VALUE" => "<pre>" . print_r($data, 1) . "</pre>"//Aktikom_Class_log::prr($data)//$data
            );
            Aktikom_Class_log::create_log_file('data_test', $type_folder, $arMass);
        }
    }

    // логирование приходящего массива
    // проверка на пустые ячейки в приходящем массиве

    private static function is_error_check($data){
        $error_text = "";
        if($data){
            if(is_array($data)){
                foreach($data as $key => $value){
                    if(is_array($value)){
                        if($value != array()){
                            $error_text_ = Aktikom_Class_log::is_error_check($value);
                            $error_text .= ' ячейка ' . $key . $error_text_ . "\n";
                        }
                    }
                    else{
                        if($value == ""){
                            $error_text = " данная ячейка пустая ";
                        }
                    }
                }
            }
            else{
                $error_text = ($data == "") ? " данная ячейка пустая " : "" ;
            }
        }
        else{
            $error_text = " данная ячейка пустая " ;
        }
        return $error_text;
    }
    public static function check_array($type, $key, $value){
        // require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
      //  CModule::IncludeModule("akticomwebservices");
        // $log_error_element  = COption::GetOptionString("akticomwebservices", "log_error_element");
        if($log_error_element == "Y"){
            $str_error_ = "";
            if(!$value){
                $data = array(
                    "DATE" => date("d-m-Y H:i:s"),
                    "VALUE" => $key . " пустая ячейка"
                );
                Aktikom_Class_log::create_log_file("error_element", $type, $data);
            }
            else{
                $str_error_ = Aktikom_Class_log::is_error_check($value);
                $str_error = "В строке " . $key . " " . $str_error_;
                if($str_error_ != ""){
                    $data = array(
                        "DATE" => date("d-m-Y H:i:s"),
                        "VALUE" => $str_error
                    );
                    Aktikom_Class_log::create_log_file("error_element", $type, $data);
                }
            }
        }
    }

    // проверка на пустые ячейки в приходящем массиве

}


?>
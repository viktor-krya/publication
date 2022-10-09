<?

namespace WebServiceLog;

use Zend\Validator\Explode;

// use WebServiceLog\Aktikom_Class_log;



class Aktikom_Update_Element{

    public static function test(){
        echo "Тестовая функция Aktikom_Update_Element";
    }

    private static function Include_Update(){
        // echo "Start include <br>"; 
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
        include($_SERVER["DOCUMENT_ROOT"] . (file_exists("/local/modules/akticomwebservices/") ? "/local" : "/bitrix") . "/modules/akticomwebservices/classes/class.php");
        // echo "Finich include <br>";
    }
    private static function LogThis($type, $data){
        switch ($type):
            case "ok":
                Aktikom_Class_log::add_check($type, $data);
                break;
            case "error":
                Aktikom_Class_log::error_check($type, $data);
                break;
        endswitch;
        // Aktikom_Class_log::add_check()
    }

    public static function Get_List($arProp){

        Aktikom_Update_Element::Include_Update();
       
        try{
            if($arProp['FILE_LOAD'])
                $arResultJson = \Bitrix\Main\Web\Json::decode(file_get_contents('php://input'));
            else
                $arResultJson = \Bitrix\Main\Web\Json::decode(file_get_contents($arProp["FILE_URL"]));
                
        }
        catch(Explode $ex){

        }

        return $arResultJson;
    }

    public static function Update_List($arParams){

    }

    public static function Load_IMG_FTP($arProp, $img_ftp){
        $ftp_server = "185.197.35.217";
        $ftp_user_name = "akticom";
        $ftp_user_pass = "x39g61eZ3";
        $ar_img = explode('.', $img_ftp);
        if($ar_img[1] == "JPG"){
            $img_type = "jpg";
        }
        elseif($ar_img[1] == "PNG"){
            $img_type = "png";
        }
        else{
            $img_type = $ar_img[1];
        }
        $img_name = explode('\\' ,  $ar_img[0]);
        $local_file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/web-service/img/'. array_pop($img_name) . "." . $img_type;
        $server_file = str_replace('\\', '/', $img_ftp);
        $handle = fopen($local_file, 'w');

        $ftp = ftp_connect($ftp_server, 21, 360000000000);
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
        fclose($handle);
        ftp_close($ftp);
        return filesize($local_file);
        unlink($local_file);
    }
}


?>
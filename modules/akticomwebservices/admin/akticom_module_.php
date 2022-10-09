
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
 
use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

// $sTableID = "tbl_form_result_list_".md5($_REQUEST['WEB_FORM_ID']);
// $oSort = new CAdminSorting($sTableID, "ID", "desc");
// $lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/akticomwebservices/include.php"); // инициализация модуля
// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php"); // пролог модуля

// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");
$POST_RIGHT = $APPLICATION->GetGroupRight("akticomwebservices");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm("ДИЧЬ");//AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("akticomwebservices");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог

$arResult = array();
$arHEADERS = array();
$arROWS_ = array();
$arROWS = array();
switch($_GET["log_menu"]){
    case "price-ok":
        $file_link = '/upload/log-web-service/user/price/log-ok.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/price/log-ok.txt';
        $str_message = "Досп";
    break;
    case "price-error":
        $file_link = '/upload/log-web-service/user/price/log-error.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/price/log-error.txt';
        $str_message = "Досп";

    break;
    case "price-error-ar":
        $file_link = '/upload/log-web-service/user/price/log-error-element.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/price/log-error-element.txt';
        $str_message = "Досп";

    break;
    case "price-error-ar-test":
        $file_link = '/upload/log-web-service/user/price/log-data-test.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/price/log-data-test.txt';
        $str_message = "Досп";

    break;
    case "service-ok":
        $file_link = '/upload/log-web-service/user/service/log-ok.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/service/log-ok.txt';
        $str_message = "Досп";

    break;
    case "service-error":
        $file_link = '/upload/log-web-service/user/service/log-error.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/service/log-error.txt';
        $str_message = "Досп";

    break;
    case "service-error-ar":
        $file_link = '/upload/log-web-service/user/service/log-error-element.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/service/log-error-element.txt';
        $str_message = "Досп";

    break;
    case "service-error-ar-test":
        $file_link = '/upload/log-web-service/user/service/log-data-test.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/service/log-data-test.txt';
        $str_message = "Досп";

    break;
    case "stocks-ok":
        $file_link = '/upload/log-web-service/user/stocks/log-ok.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/stocks/log-ok.txt';
        $str_message = "Досп";

    break;
    case "stocks-error":
        $file_link = '/upload/log-web-service/user/stocks/log-error.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/stocks/log-error.txt';
        $str_message = "Досп";

    break;
    case "stocks-error-ar":
        $file_link = '/upload/log-web-service/user/stocks/log-error-element.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/stocks/log-error-element.txt';
        $str_message = "Досп";

    break;
    case "stocks-error-ar-test":
        $file_link = '/upload/log-web-service/user/stocks/log-data-test.txt';
        $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/log-web-service/stocks/log-data-test.txt';
        $str_message = "Досп";

    break;
}

$file_fopen = fopen($file, "r");
$constenet = fread($file_fopen, filesize($file));
fclose($file_fopen);

$count_start = 0;
$count_element = 0;
$arResult_ = explode("~", $constenet);
foreach($arResult_ as $key => $value){
    $data_this = explode("^", $value);
    if($count_start == 0){
        $count_start = count($data_this);
    }
    elseif($count_start == count($data_this)){
        $count_element ++;
    }
    $arResult[] = $data_this;
}
// echo $count_element;

$list_id = "MAin_Grid";//\Coderun\Racingchampions\ChampionsInfoTable::getTableName(); //Индификатор таблицы

//filtre
$APPLICATION->includeComponent(
    "bitrix:main.ui.filter",
    "",
    [
        "FILTER_ID" => "DEMO_FILTER",
        "GRID_ID" => $list_id,
        'FILTER' => array(
            ['id' => 'DATE', 'name' => 'Дата', 'type' => 'date'],
        ),
        // ...
    ]
);

// Получаем данные для фильтрации.
$filterOptions = new \Bitrix\Main\UI\Filter\Options("DEMO_FILTER");
$filterFields = $filterOptions->getFilter([
           ['id' => 'DATE', 'name' => 'Дата', 'type' => 'date'],
]);
//filtre

if($filterFields  != array()){
    $count_element = 0;
    $arFilter = array();
    foreach($arResult as $key => $value){
        if($key != 0){
            $date_from = strtotime($filterFields['DATE_from']);
            $date_to = strtotime($filterFields['DATE_to']);
            $date_this = strtotime(str_replace('-', ".", $value[0]));
            if($date_from <= $date_this && $date_this <= $date_to){
                $count_element ++ ;
                $arFilter[] = $value;
            }
        }
        else{
            foreach($value as $keys => $item){
                $arHEADERS[] =  array(
                    "id" => "ID_" . $keys,
                    "name" => $item, 
                    'sort' => "ID_" . $keys, 
                    'default' => true,
                    'type' => 'text',
                );
            }
        }
    }
}
$grid_options = new GridOptions($list_id);
$nav = new Bitrix\Main\UI\PageNavigation('akticomwebservices');
$nav_params = $grid_options->GetNavParams();
$nav->allowAllRecords(false)
    ->setPageSize($nav_params['nPageSize'])
    ->setRecordCount($count_element)
    ->initFromUri();


if($nav->getPageCount()){
    $page = $nav->getCurrentPage();
}
else{
    $page = 0;
}
$index_test_hot = 0;

if($arFilter){
    $elem_end = $nav_params['nPageSize'] * $page;
    $elem_start = $elem_end - $nav_params['nPageSize'] ;
    foreach($arFilter as $key => $arItem){
        if($key >= $elem_start && $key < $elem_end){
            $arROWS_ = array();
            foreach($arItem as $keys => $item){
                $arROWS_["data"]["ID_" . $keys] = $item;
            }
            $arROWS[] = $arROWS_;
        }
    }
}
else{
    $elem_end = $nav_params['nPageSize'] * $page;
    $elem_start = $elem_end - $nav_params['nPageSize'];
    foreach($arResult as $key => $arItem){
        if($key == 0 ){
            foreach($arItem as $keys => $item){
                $arHEADERS[] =  array(
                    "id" => "ID_" . $keys,
                    "name" => $item, 
                    'sort' => "ID_" . $keys, 
                    'default' => true,
                    'type' => 'text',
                );
            }
        }
        elseif(count($arItem) != count($arHEADERS)){
            break;
        }
        else{
            if($key > $elem_start && $key <= $elem_end){
                $arROWS_ = array();
                foreach($arItem as $keys => $item){
                    $arROWS_["data"]["ID_" . $keys] = $item;
                }
                $arROWS[] = $arROWS_;
            }
        }
    
    }
}
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    array(
        "GRID_ID" => 'MAin_Grid',
        "COLUMNS" => $arHEADERS,
        "ROWS" => $arROWS,
        'NAV_OBJECT' => $nav,
        'AJAX_MODE' => 'Y', 
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''), 
        'PAGE_SIZES' => [  
            ['NAME' => "5", 'VALUE' => '5'], 
            ['NAME' => '10', 'VALUE' => '10'], 
            ['NAME' => '20', 'VALUE' => '20'], 
            ['NAME' => '50', 'VALUE' => '50'], 
            ['NAME' => '100', 'VALUE' => '100']
        ], 
        'AJAX_OPTION_JUMP'          => 'N', 
        'SHOW_CHECK_ALL_CHECKBOXES' => true, 
        'SHOW_ROW_ACTIONS_MENU'     => true, 
        'SHOW_GRID_SETTINGS_MENU'   => false, 
        'SHOW_NAVIGATION_PANEL'     => true, 
        'SHOW_PAGINATION'           => true, 
        'SHOW_SELECTED_COUNTER'     => true, 
        'SHOW_TOTAL_COUNTER'        => true, 
        'SHOW_PAGESIZE'             => true, 
        'SHOW_ACTION_PANEL'         => true, 
        'ALLOW_COLUMNS_SORT'        => true, 
        'ALLOW_COLUMNS_RESIZE'      => true, 
        'ALLOW_HORIZONTAL_SCROLL'   => true, 
        'ALLOW_SORT'                => false, 
        'ALLOW_PIN_HEADER'          => true, 
        'AJAX_OPTION_HISTORY'       => 'Y' 
    )
);

?>
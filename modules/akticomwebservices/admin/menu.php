<?

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$module_id = "akticomwebservices";
$RIGHT = $APPLICATION->GetGroupRight($module_id);

if($RIGHT >= "R") {
    AddEventHandler("main", "OnBuildGlobalMenu", "AkticomGlobalMenu");
    if (!function_exists("AkticomGlobalMenu")) {
        function AkticomGlobalMenu(&$aGlobalMenu, &$aModuleMenu){
            $log_ok  = COption::GetOptionString("akticomwebservices", "log_ok_print");
            $log_error  = COption::GetOptionString("akticomwebservices", "log_error_print");
            $log_error_element  = COption::GetOptionString("akticomwebservices", "log_error_element_print");
            $log_data  = COption::GetOptionString("akticomwebservices", "log_data_print");
            $arMenu = array();
            
            if($log_ok == "Y"){
                $arMenu["LOG_PRICE"][] = array(
                    'parent_menu' => "menu_price",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=price-ok',
                    'text' => "Лог нормальной работы WEB сервиса",
                    'title' => "Лог нормальной работы WEB сервиса",
                    "items_id" => 'menu_price_ok'
                );
                $arMenu["LOG_SERVICE"][] = array(
                    'parent_menu' => "menu_service",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=service-ok',
                    'text' => "Лог нормальной работы WEB сервиса",
                    'title' => "Лог нормальной работы WEB сервиса",
                    "items_id" => 'menu_service_ok'
                );
                $arMenu["LOG_STOCKS"][] = array(
                    'parent_menu' => "menu_stocks",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=stocks-ok',
                    'text' => "Лог нормальной работы WEB сервиса",
                    'title' => "Лог нормальной работы WEB сервиса",
                    "items_id" => 'menu_stocks_ok'
                );
            }
            if($log_error == "Y"){
                $arMenu["LOG_PRICE"][] = array(
                    'parent_menu' => "menu_price",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=price-error',
                    'text' => "Лог ошибок работы WEB сервиса",
                    'title' => "Лог ошибок работы WEB сервиса",
                    "items_id" => 'menu_price_error'
                );
                $arMenu["LOG_SERVICE"][] = array(
                    'parent_menu' => "menu_service",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=service-error',
                    'text' => "Лог ошибок работы WEB сервиса",
                    'title' => "Лог ошибок работы WEB сервиса",
                    "items_id" => 'menu_service_error'
                );
                $arMenu["LOG_STOCKS"][] = array(
                    'parent_menu' => "menu_stocks",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=stocks-error',
                    'text' => "Лог ошибок работы WEB сервиса",
                    'title' => "Лог ошибок работы WEB сервиса",
                    "items_id" => 'menu_stocks_error'
                );
            }
            if($log_error_element == "Y"){
                $arMenu["LOG_PRICE"][] = array(
                    'parent_menu' => "menu_price",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=price-error-ar',
                    'text' => "Лог проверки пустых ячеек WEB сервиса",
                    'title' => "Лог проверки пустых ячеек WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_price_error_ar'
                );
                $arMenu["LOG_SERVICE"][] = array(
                    'parent_menu' => "menu_service",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=service-error-ar',
                    'text' => "Лог проверки пустых ячеек WEB сервиса",
                    'title' => "Лог проверки пустых ячеек WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_service_error_ar'
                );
                $arMenu["LOG_STOCKS"][] = array(
                    'parent_menu' => "menu_stocks",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=stocks-error-ar',
                    'text' => "Лог проверки пустых ячеек WEB сервиса",
                    'title' => "Лог проверки пустых ячеек WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_stocks_error_ar'
                );
            }
            if($log_data == "Y"){
                $arMenu["LOG_PRICE"][] = array(
                    'parent_menu' => "menu_price",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=price-error-ar-test',
                    'text' => "Лог приходящего массива данных WEB сервиса",
                    'title' => "Лог приходящего массива данных WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_price_error_ar_data'
                );
                $arMenu["LOG_SERVICE"][] = array(
                    'parent_menu' => "menu_service",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=service-error-ar-test',
                    'text' => "Лог приходящего массива данных WEB сервиса",
                    'title' => "Лог приходящего массива данных WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_service_error_ar_data'
                );
                $arMenu["LOG_STOCKS"][] = array(
                    'parent_menu' => "menu_stocks",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=stocks-error-ar-test',
                    'text' => "Лог приходящего массива данных WEB сервиса",
                    'title' => "Лог приходящего массива данных WEB сервиса",
                    // 'icon' => 'fav_menu_icon',
                    // 'page_icon' => 'fav_menu_icon',
                    "items_id" => 'menu_stocks_error_ar_data'
                );
            }
        $aMenu['global_menu_akticom'] = array(
            'menu_id' => 'akticom',
            'text' => "Akticom loger WEB service",
            'title' => "Akticom loger WEB service",
            'url' => 'akticom_module_.php?lang=ru',
            'sort' => 5000,
            'items_id' => "global_menu_akticom",
            'help_section' => 'akticom',
            'items' => array(
                array(
                    'parent_menu' => "global_menu_akticom",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=price-ok',
                    'text' => "Выполнение выгрузки WEB сервиса цены",
                    'title' => "Выполнение выгрузки цены title",
                    "items_id" => 'menu_price',
                    'items' => $arMenu["LOG_PRICE"]
                ),
                array(
                    'parent_menu' => "global_menu_akticom",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=service-ok',
                    'text' => "Выполнение выгрузки WEB сервиса элементов",
                    'title' => "Выполнение выгрузки цены title",
                    "items_id" => 'menu_service',
                    'items' => $arMenu["LOG_SERVICE"]
                ),
                array(
                    'parent_menu' => "global_menu_akticom",
                    'sort' => 100,
                    "url" => 'akticom_module_.php?lang=ru&log_menu=stocks-ok',
                    'text' => "Выполнение выгрузки WEB сервиса остков по складам",
                    'title' => "Выполнение выгрузки цены title",
                    "items_id" => 'menu_stocks',
                    'items' => $arMenu["LOG_STOCKS"]
                )
            )
        );
        return $aMenu;
        }
    }
}

?>
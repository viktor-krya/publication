<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

// получаем идентификатор модуля
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);
// подключаем наш модуль
Loader::includeModule($module_id);



$aTabs = array(
    array(
        /*
         * Первая вкладка «Основные настройки»
         */
        'DIV'     => 'edit1',
        'TAB'     => Loc::getMessage('SCROLLUP_OPTIONS_TAB_GENERAL'),
        'TITLE'   => Loc::getMessage('SCROLLUP_OPTIONS_TAB_GENERAL'),
        'OPTIONS' => array(
            array(
                "log_ok",
                "Логировать отработку WEB сервиса",
                "Y",
                array("checkbox", 25)
            ),
            array(
                "log_ok_print",
                "Выводить лог успешной работы WEB сервиса",
                "Y",
                array("checkbox", 25)
            ),
            array(
                "log_error",
                "Логировать ошибки WEB сервисов",
                "Y",
                array("checkbox", 25)
            ),
            array(
                "log_error_print",
                "Выводить лог ошибок работы WEB сервиса",
                "Y",
                array("checkbox", 25)
            ),
            array(
                "log_error_element",
                "Проверять наличие пустых ячеек в приходящем массиве",
                "N",
                array("checkbox", 25)
            ),
            array(
                "log_error_element_print",
                "Выводить лог пустых ячеек приходящего массива",
                "N",
                array("checkbox", 25)
            ),
            array(
                "log_data",
                "Логировать приходящий массив",
                "Y",
                array("checkbox", 25)
            ),
            array(
                "log_data_print",
                "Выводить лог приходящего массива",
                "Y",
                array("checkbox", 25)
            ),
        )
    ),
);

$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);


$tabControl->begin();
?>
<form action="<?= $APPLICATION->getCurPage(); ?>?mid=<?=$module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
    <?= bitrix_sessid_post(); ?>
    <?php
    foreach ($aTabs as $aTab) { // цикл по вкладкам
        if ($aTab['OPTIONS']) {
            $tabControl->beginNextTab();
            __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
        }
    }
    $tabControl->buttons();
    ?>
    <input type="submit" name="apply" 
           value="<?= Loc::GetMessage('SCROLLUP_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save" />
    <input type="submit" name="default"
           value="<?= Loc::GetMessage('SCROLLUP_OPTIONS_INPUT_DEFAULT'); ?>" />
</form>

<?php
$tabControl->end();

/*
 * Обрабатываем данные после отправки формы
 */
if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) { // цикл по вкладкам
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) { // если это название секции
                continue;
            }
            if ($arOption['note']) { // если это примечание
                continue;
            }
            if ($request['apply']) { // сохраняем введенные настройки
                $optionValue = $request->getPost($arOption[0]);
                if ($arOption[0] == 'switch_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                if ($arOption[0] == 'jquery_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } elseif ($request['default']) { // устанавливаем по умолчанию
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->getCurPage().'?mid='.$module_id.'&lang='.LANGUAGE_ID);

}
?>
<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<? if(!$arResult['CHECK']): ?>
    <button class="non_check" onclick='V_TEST_check(<?= $arResult["ID_HB"] ?>, <?= $arResult["ID_ELEMENT"] ?>, 0,<?= $arResult["AYAX"] ?>,<?= $arResult["USER_ID"] ?>)'><?= GetMessage('TEXT_1') ?></button>
<? else: ?>
    <button class="yes_check" onclick='V_TEST_check(<?= $arResult["ID_HB"] ?>, <?= $arResult["ID_ELEMENT"] ?>, <?= $arResult["CHECK"] ?>,<?= $arResult["AYAX"] ?>,<?= $arResult["USER_ID"] ?>)'><?= GetMessage('TEXT_2') ?></button>
<? endif; ?>

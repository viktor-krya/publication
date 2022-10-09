<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<? use \Bitrix\Main\Localization\Loc; ?>
<?
if($arParams['NAME_PAGE'] != ""){
    $APPLICATION->SetTitle($arParams['NAME_PAGE']);
}
?>
<section>

<? if(empty($arResult)): ?>
    <div>
        <?= Loc::GetMessage("TEXT_NO_AUTH") ?>
    </div>
    <div>
        <a href="/login/?login=yes&backurl=%2Flogin%2F"><?= GetMessage('TEXT_AUTH_HREF') ?></a>
        &nbsp;/&nbsp;        
        <a href="/login/?register=yes&backurl=%2Flogin%2F"><?= GetMessage('TEXT_REG_HREF') ?></a>
    </div>

<? else: ?>
    <div class="content_list">
    
    <? foreach($arResult['LIST_ELEMENT'] as $item): ?>
        <? $photo = CFile::ResizeImageGet($item['DETAIL_PICTURE'], array('width' => 300, 'height' => 200), BX_RESIZE_IMAGE_EXACT, false); ?>
        <a href="<?= $item['DETAIL_PAGE_URL'] ?>" class="content_list__element">
            <img src="<?= $photo['src'] ?>" alt="">
            <p><?= $item['NAME'] ?></p>
        </a>
    <? endforeach; ?>
</div>
</section>


<? endif; ?>

<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule("iblock");


$rsBlock = CIBlock::GetList(
    $arOrder  = array("SORT" => "ASC"),
    $arFilter,
    true
);
while($arBlocks = $rsBlock->fetch()) {
    $arIblockID[$arBlocks['ID']] = '[' . $arBlocks['ID'] . '] ' . $arBlocks['NAME'];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "SETTINGS" => array(
            "NAME" => GetMessage("SETTINGS_PHR")
        ),
    ),
    
    "PARAMETERS" => array(
       
         "IBLOCK_ID" => array(
            "PARENT" => "SETTINGS",
            "NAME" => GetMessage("IBLOCK_ID_NAME"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIblockID,
            "REFRESH" => "Y"
         ),
        "ELEMENT_LIST" => array(
            "PARENT" => 'SETTINGS',
            "NAME"   => GetMessage('ID_DATA_LIST_NAME'),
            "TYPE"   => "STRING",
        ),
        "NAME_PAGE" => array(
            "PARENT" => 'SETTINGS',
            "NAME"   => GetMessage('NAME_PAGE_NAME'),
            "TYPE"   => "STRING"
        ),
        
    )
);


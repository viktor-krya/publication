<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock as HL; 


class V_TEST extends CBitrixComponent{

    public function onPrepareComponentParams($params)
	{
        
		// $arResult['AUTH_USER'] = $this->AuthCheckUser();
        
        if($this->AuthCheckUser()){
            $arResult = $this->ListData($params['ELEMENT_LIST']);
            $arResult['LIST_ELEMENT'] = $this->GetElementCatalog($params["IBLOCK_ID"], $arResult['LIST_ELEMENT_CATALOG_ID']);
        }
        else{
            $arResult = array();
        }
        $this->arResult = $arResult;
		return $params;
	}
   
    public function executeComponent()
	{
		$this->includeComponentTemplate();
	}

    private function AuthCheckUser(){
        $user = $GLOBALS['USER'];
        return $user->isAuthorized();
    }
    private function GetUser(){
        $user = $GLOBALS['USER'];
        return $user->GetID();
    }


    private function GetElementCatalog($IDiblock, $IDList){
        if(!empty($IDList)){
            $rsElement = CIBlockElement::GetList(
                $arOrder  = array("SORT" => "ASC"),
                $arFilter = array(
                    "ACTIVE"    => "Y",
                    "ID" => $IDList,
                    "IBLOCK_ID" => $IDiblock
                ),
                false,
                false,
                array(
                    "ID", 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL'
                )
            );
            while($arElement = $rsElement->GetNextElement()) {
                $result[$arElement->GetFields()["ID"]] = $arElement->GetFields();
            }
        
        
            return $result;
        }
    }

    private function ListData($hlbl){
        $result['ITEMS'] = array();
        $result['LIST_ELEMENT_CATALOG_ID'] = array();
        CModule::IncludeModule("mohighloadblockduleName");
        $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 

        $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
        $entity_data_class = $entity->getDataClass(); 
        $rsData = $entity_data_class::getList(array(
            "select" => array("*"),
            "filter" => array('UF_ID_USER' => $this->GetUser()),
            "order" => array("ID" => "ASC"),
        ));
        while($arData = $rsData->Fetch()){
            $result["ITEMS"] = $arData; 
            
            foreach($arData['UF_ID_ELEMENT_LIST'] as $arItem){
                if(!in_array($arItem, $result['LIST_ELEMENT_CATALOG']))
                    $result['LIST_ELEMENT_CATALOG_ID'][] = $arItem;
            }
        }
        return $result;
    }
}
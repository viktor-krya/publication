<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock as HL; 
class V_TEST_Button extends CBitrixComponent{
    public function onPrepareComponentParams($params)
	{

        $this->arResult = [
            "ID_HB"      => $params['ID_HB'],
            "ID_ELEMENT" => $params["ID_ELEMENT"],
            "CHECK"      => $this->CheckElement($params['ID_HB'], $params["ID_ELEMENT"]),
            'AYAX'       => ($params['AYAX'] == "Y") ? 1 : 0,
            'USER_ID'    => $this->GetUser()
        ];
        return $params;
    }
    private function GetUser(){
        $user = $GLOBALS['USER'];
        return $user->GetID();
    }
    private function CheckElement($hlbl, $IDElement){
        CModule::IncludeModule("mohighloadblockduleName");
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch(); 

        $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
        $entity_data_class = $entity->getDataClass(); 
        $rsData = $entity_data_class::getList(array(
            "select" => array("*"),
            "filter" => array('UF_ID_USER' => $this->GetUser()),
            "order" => array("ID" => "ASC"),
        ));
        while($arData = $rsData->Fetch()){
            if(in_array($IDElement, $arData['UF_ID_ELEMENT_LIST']))
                return true;
            else
                return false;
        }
    }

    public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}
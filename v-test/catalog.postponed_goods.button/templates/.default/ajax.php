<? 
use Bitrix\Highloadblock as HL; 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


/*
"HB_block": hb,
"ID_element" : id_e,
"ID_user" : id_user,
"Check" : check 
*/
echo "<pre>" . print_r($_POST,1) . "</pre>";

$result = array();
CModule::IncludeModule('main');
CModule::IncludeModule('highloadblock');
CModule::IncludeModule("mohighloadblockduleName");
$hlblock = HL\HighloadBlockTable::getById($_POST['HB_block'])->fetch(); 

$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data_class = $entity->getDataClass();
$rsData = $entity_data_class::getList(array(
    "select" => array("*"),
    "filter" => array('UF_ID_USER' => $_POST['ID_user']),
    "order" => array("ID" => "ASC"),
));
while($arData = $rsData->Fetch()){
    $result = $arData;
}
echo "<pre>" . print_r($result,1) . "</pre>";

$data['UF_ID_USER'] = $_POST['ID_user'];
if(!empty($result)){ 
    echo "1";
    if($_POST['Check']){
        echo "1-1";
        $key_this = array_keys($result['UF_ID_ELEMENT_LIST'], $_POST['ID_element'])[0];
        unset($result['UF_ID_ELEMENT_LIST'][$key_this]);
        echo "<br>" . $key_this . "<br>";
        echo "<pre>" . print_r($key_this,1) . "</pre>";
        echo "<pre>" . print_r($result['UF_ID_ELEMENT_LIST'][$key_this],1) . "</pre>";
        
    }
    else{
        echo "1=2";
        $result['UF_ID_ELEMENT_LIST'][] =$_POST['ID_element'];
    }
    $data['UF_ID_ELEMENT_LIST'] = $result['UF_ID_ELEMENT_LIST'];
    $entity_data_class::update($result['ID'], $data);
}
else{
    echo "2"; 
    $data['UF_ID_ELEMENT_LIST'][] =$_POST['ID_element'];
    $entity_data_class::add($data);
}
echo "<pre>" . print_r($result,1) . "</pre>";
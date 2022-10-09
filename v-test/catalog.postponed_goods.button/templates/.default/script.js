function V_TEST_check(hb, id_e, check, ajax_check, id_user){
    console.log(hb + 'script');
    console.log(id_e + 'script');
    console.log( check+ 'script');
    console.log(ajax_check+  'script');
    console.log( id_user + 'script');
    var data_s = [{
        "HB_block": hb,
        "ID_element" : id_e,
        "ID_user" : id_user,
        "Check" : check
    }];
    var data = new FormData();

    data.append("HB_block", hb);
    data.append("ID_element", id_e);
    data.append("ID_user", id_user);
    data.append("Check", check);
    var xhr = new XMLHttpRequest();
   
    xhr.open("POST", "/local/components/v-test/catalog.postponed_goods.button/templates/.default/ajax.php", false, );
    xhr.onreadystatechange = function(){
        if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200){
          console.log(xhr.responseText);
          location.reload();
        }
      }
    xhr.send(data);
}
<?php

if ($_SERVER['REQUEST_METHOD']=="POST") {
    $function = $_POST["call"];
    $codecorresp_post = $_POST["code_corresp"];

    if(function_exists($function)) {
        call_user_func($function,$codecorresp_post);
    } else {
        echo 'Function not exist';
    }
}
function detail_corresp($codecorresp) {

    $conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

    if (!$conn) {
         $e = oci_error();
         trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
     }
    $req = "select * from CORRESPONDANTS where code_corresp = '".$codecorresp."'" ;

    $stid = oci_parse($conn, $req);
    oci_execute($stid);
    $nrows = oci_fetch_all($stid, $results);

    
}

?>
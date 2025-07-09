<?php


 $name = "pstest";
 $mdp ="ennov";
 $server = "TRA_ENNOV_01_R";
 $encoding = "utf8";

$conn = oci_connect($name, $mdp, $server, $encoding);

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}


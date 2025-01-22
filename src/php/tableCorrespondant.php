<?php
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $function = $_POST["call"];

    if (function_exists($function)) {
        call_user_func($function);
    } else {
        echo 'Function Not Exists!!';
    }
}

function tableCorrespondantShow()
{
    // connect();
    $conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

    if (!$conn) {
         $e = oci_error();
         trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
     }
    $req = "select * from labos where ville = 'LOOS'";

    $stid = oci_parse($conn, $req);
    oci_execute($stid);
    $nrows = oci_fetch_all($stid, $results);

    echo "Connexion réussie";

    oci_free_statement($stid);
    oci_close($conn);
}

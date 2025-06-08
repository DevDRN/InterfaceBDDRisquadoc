<?php

declare(strict_types=1);

$conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}


//Traitement : ajout / suppression / réiniti
<?php
function connect () {

$conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
$req = "select * from labos where ville = 'LOOS'";

$stid = oci_parse($conn, $req);
oci_execute($stid);
echo "Connexion réussie";

oci_free_statement($stid);
oci_close($conn);

}
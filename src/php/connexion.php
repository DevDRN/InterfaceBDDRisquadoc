<?php
 function connect(): OCI_Connexion {

 $name = "pstest";
 $mdp ="ennov";
 $server = '(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = rac5.chrul.net)(PORT = 1521))'.'(CONNECT_DATA = (SERVICE_NAME = TRA_ENNOV_01_R)))';
 $encoding = "utf8";

$conn = oci_connect($name, $mdp, $server, $encoding);

if (!$conn) {
    $e = oci_error();
throw new RuntimeException('Erreur de connexion à la BDD : ' .htmlspecialchars($e['message'])) ;
}
return $conn;
}
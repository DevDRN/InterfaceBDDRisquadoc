<?php

require_once __DIR__ . '\connexion.php';
require_once __DIR__ . '\sendMail.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    //Vérifier si l'email existe
    $sql = "SELECT MATRICULE, EMAIL FROM USERS WHERE EMAIL = :email";
    $stid = oci_parse($conn,$sql);
    oci_bind_by_name($stid, ":email", $email);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);

    if ($row) {
        //Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", time() + 3600); //valide 1h

        //Stocker le token dans la table PASSWORD_RESETS
        $insert = "INSERT INTO PASSWORD_RESETS (EMAIL, TOKEN, EXPIRE_AT) VALUES (:email, :token, TO_DATE(:expires, 'YYYY-MM-DD HH24:MI:SS'))";
        $stmt = oci_parse($conn, $insert);
    }
}
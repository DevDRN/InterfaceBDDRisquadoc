<?php

require "init.php";

if (!isset($_SESSION['MATRICULE'])) {
    header('Location: ../../index.php');
    exit;
}
require "connexion.php";

$matricule = $_SESSION['MATRICULE'];
$errors = [];
$message = [];

//Récup des données existantes
$infoStmt = oci_parse($conn, 'SELECT * FROM USERS WHERE MATRICULE = :matricule');
oci_bind_by_name($infoStmt, ':matricule', $matricule);
oci_execute($infoStmt);
$user = oci_fetch_assoc($infoStmt);
oci_free_statement($infoStmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nom = trim($_POST['NOM'] ?? '');
    $prenom = trim($_POST['PRENOM'] ?? '');
    $email = trim($_POST['EMAIL'] ?? '');
    $newMdp = trim($_POST['MDP'] ?? '');

    if (!$prenom || !$nom) $errors[] = 'Nom et prénom requis.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
    if ($newMdp && strlen($newMdp) < 6) $errors[] = 'Mot de passe trop court (6+ caractères).';

    if (empty($errors)) {
        $username = strtolower("$prenom.$nom");
        $mdp_hash = $newMdp ? password_hash($newMdp, PASSWORD_BCRYPT) : $user['MDP'];
    }

}
?>
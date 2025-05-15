<?php

declare(strict_types=1);

$conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$erreurs = [];
$success ='';
$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit($id)) {
    die('Identifiant Invalide.');
}
$id = (int) $id;
var_dump($id);

//Traitement de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $sqlDel = "DELETE FROM CORRESPONDANTS WHERE CODE_CORRESP = :id";
    $stmtDel = oci_parse($conn, $sqlDel);
    oci_bind_by_name($stmtDel,':id', $id);
    $okDel = oci_execute($stmtDel, OCI_COMMIT_ON_SUCCESS);
    oci_free_statement($stmtDel);

    if($okDel) {
        //redirection après suppression
        header('Location: dashboard.php?msg=suppr_ok');
        exit;
    } else {
        $e = oci_error($stmtDel);
        $erreurs[] = 'Erreur lors de la suppression: '.htmlentities($e['message'], ENT_QUOTES);
    }
}

//Traitement de MàJ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    //Recup et clear
    $codeCorresp = trim($_POST['codeCorresp'] ?? '');
    $codeLabo = trim($_POST['codeLabo'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telMobile = trim($_POST['telMobile'] ?? '');
    $telFixe = trim($_POST['telFixe'] ?? '');
    $email = trim($_POST['email'] ?? '');

    //Validation simple
    if ($codeCorresp === '') {
        $erreurs[] = 'Code Correspondant requis.';
    }
    if ($codeLabo === '') {
        $erreurs[] = 'Code Labo requis.';
    }
    if ($nom === '') {
        $erreurs[] = 'Le nom est requis.';
    }
    if ($telFixe === '' && $telMobile === '') {
        $erreurs[] = 'Au moins un numero de telephone est requis ';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'email est invalide';
    }

    if (empty($erreurs)) {
        $sqlUpd = "UPDATE CORRESPONDANTS
                    SET CODE_CORRESP = :codeCorresp,
                        CODE_LABO = :codeLabo,
                        TITRE = :titre,
                        NOM = :nom,
                        PRENOM = :prenom,
                        TEL_MOBILE_CORRESP = :telMobile,
                        TEL_FIXE_CORRESP = :telFixe,
                        MAIL_CORRESP = :email;";

        $stmtUpd = oci_parse($conn,$sqlUpd);

        oci_bind_by_name($stmtUpd, ':codeCorresp', $codeCorresp);
        oci_bind_by_name($stmtUpd, ':codeLabo', $codeLabo);
        oci_bind_by_name($stmtUpd, ':titre', $titre);
        oci_bind_by_name($stmtUpd, ':nom', $nom);
        oci_bind_by_name($stmtUpd, ':prenom', $prenom);
        oci_bind_by_name($stmtUpd, ':telMobile', $telMobile);
        oci_bind_by_name($stmtUpd, ':telFixe', $telFixe);
        oci_bind_by_name($stmtUpd, ':email', $email);

        $okUpd = oci_execute($stmtUpd, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($stmtUpd);

        if($okUpd) {
            $success ='Le correspondant a été mis à jour avec succès.';
        } else {
            $e = oci_error($stmtUpd);

        }

    }
}
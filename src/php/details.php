<?php

/* if ($_SERVER['REQUEST_METHOD']=="POST") {
    $function = $_POST["call"];
    $codecorresp_post = $_POST["code_corresp"];

    if(function_exists($function)) {
        call_user_func($function,$codecorresp_post);
    } else {
        echo 'Function not exist';
    }
} */
/* function detail_corresp($codecorresp) {

    $conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

    if (!$conn) {
         $e = oci_error();
         trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
     }
    $req = "select * from CORRESPONDANTS where code_corresp = '".$codecorresp."'" ;

    $stid = oci_parse($conn, $req);
    oci_execute($stid);
    $nrows = oci_fetch_all($stid, $results);

    
} */
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');

if(!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    if(!headers_sent()) {
        header('Location: dashboard.php');
        exit;
    }
    echo '<p>Identifiant invalide</p>';
    exit;
}
$id=(int)$_GET['id'];
var_dump($id );

$conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
$req = "select * from CORRESPONDANTS where code_corresp = ".$id."" ;
var_dump($req);

$stid = oci_parse($conn, $req);
oci_execute($stid);
$nrows = oci_fetch_all($stid, $results);
var_dump($results);

if (!$nrows) {
    echo '<p>Correspondant non trouvé.</p>';
    exit;
}

?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
  <script src="../js/color-modes.js"></script>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="DevClownJP">
  <meta name="generator" content="Hugo 0.118.2">
  <title>InterfaceBDDRisquadoc</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

  <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/dashboard.css">

  <!-- Favicons -->
  <link rel="apple-touch-icon" href="/docs/5.3/assets/img/favicons/apple-touch-icon.png" sizes="180x180">
  <link rel="icon" href="/docs/5.3/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="/docs/5.3/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
  <link rel="manifest" href="/docs/5.3/assets/img/favicons/manifest.json">
  <link rel="mask-icon" href="/docs/5.3/assets/img/favicons/safari-pinned-tab.svg" color="#712cf9">
  <link rel="icon" href="/docs/5.3/assets/img/favicons/favicon.ico">
  <link src="../../node_modules/sweetalert2/dist/sweetalert2.min.css">
  <meta name="theme-color" content="#712cf9">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../node_modules/jquery/dist/jquery.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body class="p-4">

    <h1>Détails du correspondant :<?= $results["NOM"][0] ?> <?= $results["PRENOM"][0] ?></h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">Code Correspondant</th>
                <th scope="col">Code Labo</th>
                <th scope="col">Titre</th>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
            </tr>
        </thead>
        <tbody>
            <tr style="height: 52px;">
                <td class="u-table-cell"><?= $results["CODE_CORRESP"][0] ?></td>
                <td class="u-table-cell"><?= $results["CODE_LABO"][0] ?></td>
                <td class="u-table-cell"><?= $results["TITRE"][0] ?></td>
                <td class="u-table-cell"><?= $results["NOM"][0] ?></td>
                <td class="u-table-cell"><?= $results["PRENOM"][0] ?></td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th scope="col">Telephone mobile</th>
                <th scope="col">Telephone fixe</th>
                <th scope="col">Mail</th>
            </tr>
        </thead>
        <tbody>
            <tr style="height: 52px;">
                <td class="u-table-cell"><?= $results["TEL_MOBILE_CORRESP"][0] ?></td>
                <td class="u-table-cell"><?= $results["TEL_FIXE_CORRESP"][0] ?></td>
                <td class="u-table-cell"><?= $results["MAIL_CORRESP"][0] ?></td>
            </tr>
        </tbody>
    </table>
    
    <a href="dashboard.php" class="btn btn-secondary">Retour à liste</a>

</body>
</html>
<?php
oci_free_statement($stid);
oci_close($conn);
?>
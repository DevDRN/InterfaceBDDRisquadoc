<?php
declare(strict_types=1);

require_once __DIR__ . '../../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require_once __DIR__ . '/../../vendor/autoload.php';
require 'init.php';
require_once __DIR__ . '\connexion.php';

//Accès restreint

if ($_SESSION['ROLES'] !== 'admin') {
    http_response_code(403);
    exit('Accès interdit');
}

//Connexion
try {
    $conn = connect();
} catch (RuntimeException $e) {
    die(htmlspecialchars($e->getMessage()));
}


//Traitement : ajout / suppression / réiniti
$message = [];
$error = [];

 function sendWelcomeEmail(string $email, string $nom, string $prenom, string $username, string $subject, string $body): bool {
  $mail= new PHPMailer(true);
    $headers = "From: noreply@looklab.com\r\n";
    try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.office365.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'svc_power365@chu-lille.fr';
      $mail->Password   = '7qNbmYQ7kDCuzfnsNF$UxHabe9Bpv*';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;
      $mail->CharSet    = 'UTF-8';
      $mail->Encoding   = 'base64';

      $mail->setFrom('svc_power365@chu-lille.fr', 'GIL');
      //$mail->Sender = 'svc_power365@chu-lille.fr';
      $mail->addAddress($email, $prenom);
      $mail->isHTML(true);
      $mail->Subject  = $subject;
      $mail->Body     = nl2br($body);
      $mail->AltBody  = $body;

      return $mail->send();
    } catch (Exception $e) {
      error_log('PHPMailer Error: ' . $mail->ErrorInfo);
      return false;
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $email = trim($_POST['email']);
        $matricule = trim($_POST['matricule']);
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $roles = $_POST['roles'];
        $username = strtolower("$prenom.$nom");
        $pwd_hash = password_hash('Chang3M3!', PASSWORD_BCRYPT);
        
        //Controles
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = 'Email invalide';
        }
        if (!in_array($roles, ['admin','membre','visiteur'])) {
            $error[] = 'Rôle invalide';
        }
        if (empty($error)) {
            $sql = 'INSERT INTO USERS (MATRICULE, USERNAME, MDP, NOM, PRENOM, EMAIL, ROLES)
            VALUES (:matricule, :username, :pwd_hash, :nom, :prenom, :email, :roles)';
            $stid = oci_parse($conn, $sql);

            //liaison
            oci_bind_by_name($stid, ':matricule', $matricule);
            oci_bind_by_name($stid, ':username', $username);
            oci_bind_by_name($stid, ':pwd_hash', $pwd_hash);
            oci_bind_by_name($stid, ':nom', $nom);
            oci_bind_by_name($stid, ':prenom', $prenom);
            oci_bind_by_name($stid, ':email', $email);
            oci_bind_by_name($stid, ':roles', $roles);

            //Verif PK
            $checkSql = 'SELECT COUNT(*) AS CNT FROM USERS WHERE MATRICULE = :matricule';
            $checkST = oci_parse($conn, $checkSql);
            oci_bind_by_name($checkST, ':matricule', $matricule);
            oci_execute($checkST);
            $checkrows = oci_fetch_assoc($checkST);
            if ($checkrows['CNT'] > 0){
                $error[] = "Le matricule <<{$matricule}>> existe déjà.";
            }
            $ok = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);

            if ($ok && intval($checkrows['CNT'] === '0')) {
                $message[] = "Utilisateur ajoutée.";

                $subject = "GIL - Bienvenue, votre compte a été créé.";
                $body = "Bonjour $prenom,\r\n\r\n Votre compte a été créé.\r\nLogin: $username \r\nMot de passe temporaire: Chang3M3! \r\nMerci de le modifier.\r\n http://10.49.22.125/";
                if (!sendWelcomeEmail($email,$nom,$prenom,$username,$subject,$body)) {
                    $error[] = "Échec de l'envoi de mail";
                }
               
            } else {
                $err = oci_error($stid);
                if ($err && isset($err['message'])){

                $error[] = 'Erreur Oracle: '.htmlspecialchars($err['message'], ENT_QUOTES|ENT_SUBSTITUTE);
                } else {
                  
                  $error[] = "Erreur inconnue avec Oracle";
                }
            }
            
            oci_free_statement($stid);
        }
    }
    elseif (isset($_POST['reset_id'])) {
        $new = password_hash('NewP@ss123', PASSWORD_BCRYPT);
        $sql = "UPDATE USERS SET MDP = :mdp WHERE MATRICULE = :matricule";
        $stm = oci_parse($conn, $sql);
        oci_bind_by_name($stm, ':mdp', $new);
        oci_bind_by_name($stm, ':matricule', $_POST['reset_id']);
        if(oci_execute($stm, OCI_COMMIT_ON_SUCCESS)){
          $message[] = "Mot de passe modifié";

          $subject = "$username, votre mot de passe a été modifié.";
          $body = "Bonjour $prenom,\r\n\r\n Votre mot de passe a été modifié.\r\nMot de passe temporaire: NewP@ss123 \r\nMerci de le modifier.\r\n";
          if (!sendWelcomeEmail($email,$nom,$prenom,$username,$subject,$body)) {
              $error[] = "Échec de l'envoi de mail";
          }

        }else {
            $e = oci_error($stm);
            $error[] = htmlentities($e['message']);
        }
        oci_free_statement($stm);
    }
    elseif (isset($_POST['delete_id'])) {
        $sql = "DELETE FROM USERS WHERE MATRICULE = :matricule";
        $stm = oci_parse($conn, $sql);
        oci_bind_by_name($stm, ':matricule',$_POST['delete_id']);
        //oci_bind_by_name($stm, ':nom', $nom);
        //oci_bind_by_name($stm, ':prenom', $prenom);
        if(oci_execute($stm, OCI_COMMIT_ON_SUCCESS)){
            $message[] = "L'utilisateur a été supprimer.";
        }else {
            $e = oci_error($stm);
            $error[] = htmlentities($e['message']);
        }
        oci_free_statement($stm);
    }
}

//Recup USERS
$stmt = oci_parse($conn, "SELECT MATRICULE, USERNAME, NOM, PRENOM, EMAIL, ROLES FROM USERS ORDER BY MATRICULE");
oci_execute($stmt);
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

  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="check2" viewBox="0 0 16 16">
      <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
    </symbol>
    <symbol id="circle-half" viewBox="0 0 16 16">
      <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
      <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
      <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
    </symbol>
    <symbol id="sun-fill" viewBox="0 0 16 16">
      <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
    </symbol>
  </svg>

  <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
    <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
      <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
        <use href="#circle-half"></use>
      </svg>
      <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
          <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
            <use href="#sun-fill"></use>
          </svg>
          Light
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
          <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
            <use href="#moon-stars-fill"></use>
          </svg>
          Dark
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
          <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
            <use href="#circle-half"></use>
          </svg>
          Auto
          <svg class="bi ms-auto d-none" width="1em" height="1em">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
    </ul>
  </div>

  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="bootstrap" viewBox="0 0 118 94">
      <title>Bootstrap</title>
      <path fill-rule="evenodd" clip-rule="evenodd" d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z"></path>
    </symbol>
    <symbol id="home" viewBox="0 0 16 16">
      <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z" />
    </symbol>
    <symbol id="speedometer2" viewBox="0 0 16 16">
      <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z" />
      <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z" />
    </symbol>
    <symbol id="table" viewBox="0 0 16 16">
      <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z" />
    </symbol>
    <symbol id="people-circle" viewBox="0 0 16 16">
      <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
      <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
    </symbol>
    <symbol id="grid" viewBox="0 0 16 16">
      <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z" />
    </symbol>
  </svg>

<h1>Gestion des utilisateurs</h1>

<?php foreach ($message as $m): ?>
    <div class ="alert alert-success"><?=htmlspecialchars($m) ?></div>
    <?php endforeach;?>
    <?php if($error): ?>
        <div class="alert alert-danger"><ul><?php foreach ($error as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
    <?php endif; ?>

<!-- Formulaire d'ajout -->
<form method="post" class="row g-2 mb-4">
    <div class="col-md-2">
        <label for="">Prénom</label>
        <input id="prenom" name="prenom" class="form-control" oninput="setUsername()"required>
    </div>
    <div class="col-md-2">
        <label for="">Nom</label>
        <input id="nom" name="nom" class="form-control" oninput="setUsername()"required>
    </div>
    <div class="col-md-2">
        <label for="">Username</label>
        <input id="username" name="username" class="form-control" readonly>
    </div>
    <div class="col-md-2">
        <label for="">Email</label>
        <input name="email" type="email" class="form-control" required>
    </div>
    <div class="col-md-2">
        <label for="">Matricule</label>
        <input name="matricule" class="form-control" required>
    </div>
    <div class="col-md-2">
        <label for="">Rôle</label>
        <select id="roles" name="roles" class="form-select" required>
            <option value="admin">Administrateur</option>
            <option value="membre">Membre</option>
            <option value="visiteur">Visiteur</option>
        </select>
    </div>
    <div class="col-md-12 text-end">
        <button name="add_user" class="btn btn-success">Ajouter</button>
    </div>
</form>
<!-- Tableau des utilisateurs -->
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Username</th>
            <th>Email</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($u = oci_fetch_assoc($stmt)): ?>
            <tr>
                <td><?= htmlspecialchars($u['MATRICULE']) ?></td>
                <td><?= htmlspecialchars($u['USERNAME']) ?></td>
                <td><?= htmlspecialchars($u['EMAIL']) ?></td>
                <td><?= htmlspecialchars($u['NOM']) ?></td>
                <td><?= htmlspecialchars($u['PRENOM']) ?></td>
                <td><?= htmlspecialchars($u['ROLES']) ?></td>
                <td>
                    <form method="post" class="d-inline"><button name="reset_id" value="<?= $u['MATRICULE'] ?>" class="btn btn-warning btn-sm">Réinitialiser le mot de passe</button></form>
                    <form method="post" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?')">
                        <button name="delete_id" value="<?= $u['MATRICULE'] ?>" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

  <footer class="sticky-bottom">
      <a href="dashboard.php" class="btn btn-secondary">Retour à liste</a>
  </footer>

  <script src="../js/main.js"></script>
  <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
  <script src="https://kit.fontawesome.com/29765f633a.js" crossorigin="anonymous"></script>
  <script src="../js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
  </script>

</body>

</html>
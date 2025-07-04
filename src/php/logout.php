<?php

    session_start();
    //efface toutes les variables de session
    $_SESSION = [];
    //Supprime le cookie de session
    if (ini_get("session.use_cookie")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    header('Location: index.php');
    exit;

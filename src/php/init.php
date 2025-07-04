<?php
session_start();
if (!isset($_SESSION['USERNAME'])) {
    header('Location: login.php');
    exit;
}
$matricule = $_SESSION['MATRICULE'];
$username  = $_SESSION['USERNAME'];
$role      = $_SESSION['ROLES'];
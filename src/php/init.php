<?php
session_start();
if (!isset($_SESSION['USERNAME'])) {
    header('Location: ../../index.php');
    exit;
}
$matricule = $_SESSION['MATRICULE'];
$username  = $_SESSION['USERNAME'];
$role      = $_SESSION['ROLES'];
<?php 
    
    $server ="localhost";
    $username="root";
    $password="root";

    try
    {    
        $conn = new PDO("mysql:host=$server;dbname=testrisquadoc",$username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        echo "Connexion réussie";
    }
    catch(PDOException $e) {
        echo "Erreur : ".$e->getMessage();
    }
?>
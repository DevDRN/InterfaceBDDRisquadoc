<?php
require 'sendMail.php';
if (sendWelcomeEmail('julien.peroche@chu-lille.fr','Julien','Test','testuser','test','ceci est un test')) {
    echo "OK";
} else echo "FAIL";

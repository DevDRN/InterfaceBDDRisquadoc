<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require '../../vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
function sendWelcomeEmail(string $email, string $nom, string $prenom, string $username, string $subject, string $body): bool {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.office365.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'julien.peroche@chu-lille.fr';//'svc_power365@chu-lille.fr';                     //SMTP username
        $mail->Password   = 'E&W%pz9SA0';//'Z5BDUVwg8uwZXB';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('julien.peroche@chu-lille.fr', 'Mailer');
        $mail->addAddress($email, "$nom. .$prenom");     //Add a recipient
    /*     $mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');
    */
        //Attachments
    /*     $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
    */
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        return $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        file_put_contents(__DIR__.'/mail.log', "PHPMailer Error: ".$mail->ErrorInfo."\n", FILE_APPEND);
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
    file_put_contents(__DIR__.'/mail.log', date('c')." - Tentative envoi vers $email\n", FILE_APPEND);
    if (! $mail->send()) {
    file_put_contents(__DIR__.'/mail.log', "Erreur: ".$mail->ErrorInfo."\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__.'/mail.log', "Succès\n", FILE_APPEND);
}
}
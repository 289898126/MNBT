<?php
include("./MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');

require './mail/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

function sendEmail($to, $subject, $message) {
    global $DB,$conf;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $conf['mailhost'];
        $mail->SMTPAuth = true;
        $mail->Username = $conf['mailuser'];
        $mail->Password = $conf['mailpassword'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $conf['mailport'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($conf['mailuser'], 'MN系统');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>

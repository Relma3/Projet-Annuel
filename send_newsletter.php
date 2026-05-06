<?php
require_once 'db_connect.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$stmt = $pdo->query("SELECT email FROM newsletter");
$emails = $stmt->fetchAll();

foreach ($emails as $user) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'silverhappy.contact@gmail.com';
        $mail->Password = 'dbzpbzmnrrdfkreo';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('silverhappy.contact@gmail.com', 'Silver Happy');
        $mail->addAddress($user['email']);

        $mail->isHTML(true);
        $mail->Subject = 'Newsletter Silver Happy';

        $mail->Body = "
            <h2>Newsletter</h2>
            <p>Nouveautés de la semaine !</p>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
    }
}

echo "Newsletter envoyée !";
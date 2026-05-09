<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        header('Location: index.php?news=error');
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
        $stmt->execute([$email]);

        header('Location: index.php?news=ok');
        exit();

    } catch (PDOException $e) {

        if ($e->getCode() == 23000) {
            header('Location: index.php?news=exist');
            exit();
        }

        error_log("Erreur SQL newsletter: " . $e->getMessage());
    }

} else {
    // Si accès direct → redirection
    header('Location: index.php');
    exit();
}
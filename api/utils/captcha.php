<?php
session_start();

function generateCaptcha() {
    $a = rand(1, 9);
    $b = rand(1, 9);

    $_SESSION['captcha'] = $a + $b;

    return "$a + $b = ?";
}

function verifyCaptcha($userAnswer) {
    return isset($_SESSION['captcha']) && $userAnswer == $_SESSION['captcha'];
}
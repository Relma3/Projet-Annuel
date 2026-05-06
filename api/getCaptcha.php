<?php
session_start();

require_once 'utils/captcha.php';

$question = generateCaptcha();

echo json_encode([
    "question" => $question
]);
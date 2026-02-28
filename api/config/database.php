<?php
function getDB() {
    try {
        return new PDO(
            "mysql:host=localhost;dbname=silverhappy;charset=utf8",
            "root",
            "root", 
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "message" => "Erreur BDD : " . $e->getMessage()
        ]);
        exit;
    }
}
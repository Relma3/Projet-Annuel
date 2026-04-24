<?php
require_once 'db_connect.php';
set_time_limit(300);

try {
    $villes = ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille', 'Nice'];
    $cats = ['domicile', 'loisirs', 'sante', 'boutique'];
    $pass = password_hash('Demo123!', PASSWORD_DEFAULT);

    for ($i = 1; $i <= 40; $i++) {
        $email = "senior_test_$i@silverhappy.fr";
        $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
        $stmt->execute([$email, $pass]);
        $id = $pdo->lastInsertId();
        if($id) {
            $stmtS = $pdo->prepare("INSERT IGNORE INTO senior (id_senior, nom, prenom, ville) VALUES (?, ?, ?, ?)");
            $stmtS->execute([$id, "NomS$i", "PrenomS$i", $villes[array_rand($villes)]]);
        }
    }

    for ($j = 1; $j <= 30; $j++) {
        $email = "presta_test_$j@silverhappy.fr";
        $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
        $stmt->execute([$email, $pass]);
        $id_p = $pdo->lastInsertId();
        if($id_p) {
            $stmtP = $pdo->prepare("INSERT IGNORE INTO prestataire (id_prestataire, nom, prenom, ville, categorie, tarif_horaire, statut) VALUES (?, ?, ?, ?, ?, ?, 'valide')");
            $stmtP->execute([$id_p, "NomP$j", "PrenomP$j", $villes[array_rand($villes)], $cats[array_rand($cats)], rand(25, 60)]);
            
            $stmtServ = $pdo->prepare("INSERT INTO services (id_prestataire, nom_service, prix, ville, description) VALUES (?, ?, ?, ?, ?)");
            $stmtServ->execute([$id_p, "Service de " . $cats[array_rand($cats)], rand(20, 50), $villes[array_rand($villes)], "Description pro $j"]);
        }
    }

    $seniors = $pdo->query("SELECT id_senior FROM senior")->fetchAll(PDO::FETCH_COLUMN);
    $prestas = $pdo->query("SELECT id_prestataire FROM prestataire")->fetchAll(PDO::FETCH_COLUMN);

    for ($k = 1; $k <= 430; $k++) {
        $stmtR = $pdo->prepare("INSERT INTO reservation (id_senior, id_prestataire, date_reservation, statut, description) VALUES (?, ?, ?, ?, ?)");
        $date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days"));
        $stmtR->execute([$seniors[array_rand($seniors)], $prestas[array_rand($prestas)], $date, 'termine', "Mission auto $k"]);
    }

    echo "Injection réussie : Seniors, Prestataires, Services et Réservations créés.";

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

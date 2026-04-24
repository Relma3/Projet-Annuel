<?php
require_once 'db_connect.php';

set_time_limit(300);

try {
    $villes = ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille', 'Nice'];
    $categories = ['domicile', 'loisirs', 'sante', 'boutique'];
    $pass_hash = password_hash('Demo123!', PASSWORD_DEFAULT);

    for ($i = 1; $i <= 50; $i++) {
        $email = "senior$i@example.com";
        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
        $stmt->execute([$email, $pass_hash]);
        $id_user = $pdo->lastInsertId();

        $stmtS = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom, telephone, adresse, ville) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtS->execute([$id_user, "NomS$i", "PrenomS$i", "0601020304", "$i Rue de la Paix", $villes[array_rand($villes)]]);
    }

    for ($i = 1; $i <= 30; $i++) {
        $email = "presta$i@example.com";
        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
        $stmt->execute([$email, $pass_hash]);
        $id_user = $pdo->lastInsertId();

        $stmtP = $pdo->prepare("INSERT INTO prestataire (id_prestataire, nom, prenom, ville, categorie, tarif_horaire, statut) VALUES (?, ?, ?, ?, ?, ?, 'valide')");
        $stmtP->execute([$id_user, "NomP$i", "PrenomP$i", $villes[array_rand($villes)], $categories[array_rand($categories)], rand(20, 50)]);
    }

    $seniors = $pdo->query("SELECT id_senior FROM senior")->fetchAll(PDO::FETCH_COLUMN);
    $prestas = $pdo->query("SELECT id_prestataire FROM prestataire")->fetchAll(PDO::FETCH_COLUMN);

    for ($i = 1; $i <= 420; $i++) {
        $id_s = $seniors[array_rand($seniors)];
        $id_p = $prestas[array_rand($prestas)];
        $date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days +" . rand(0, 24) . " hours"));
        $statuts = ['en_attente', 'confirme', 'termine', 'annule'];

        $stmtR = $pdo->prepare("INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut) VALUES (?, ?, ?, ?, ?)");
        $stmtR->execute([$id_s, $id_p, $date, "Prestation de service numéro $i", $statuts[array_rand($statuts)]]);
    }

    echo "Peuplement terminé : +500 lignes insérées.";

} catch (Exception $e) {
    die($e->getMessage());
}

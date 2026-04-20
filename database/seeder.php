<?php

if (php_sapi_name() != 'cli') {
    $token = getenv('SEEDER_TOKEN') ?: 'silver2026';

    if (!isset($_GET['token']) || $_GET['token'] != $token) {
        http_response_code(403);
        die("Acces refuse");
    }

    header('Content-Type: text/plain');
}

require_once __DIR__ . '/../db_connect.php';

echo "Seeder Silver Happy\n";

$prenoms = ['Jean','Marie','Pierre','Jeanne','Michel','Sophie','Luc','Nadia','Paul','Julie'];
$noms = ['Martin','Bernard','Dubois','Thomas','Robert','Petit','Durand','Leroy','Moreau','Simon'];
$villes = ['Paris','Lyon','Marseille','Bordeaux','Nantes','Toulouse'];
$categories = ['Aide a domicile','Sante','Transport','Cuisine','Menage','Informatique'];
$articles_noms = ['Coussin ergonomique','Telephone senior','Lampe LED','Pilulier','Barre appui','Montre senior'];
$messages = [
    'Bonjour, j ai une question.',
    'Merci pour votre aide.',
    'Je souhaite annuler mon rendez-vous.',
    'Pouvez-vous me rappeler ?',
    'Je veux changer de prestataire.'
];

$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

$tables = ['rdv_medicaux','devis','messages','commandes','reservation','article','evenements','categories_prestations','conseils','prestataire','senior','utilisateur'];

foreach ($tables as $table) {
    $pdo->exec("DELETE FROM `$table`");
    try {
        $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
    } catch (Exception $e) {
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "Tables videes\n";

$hash = password_hash('Test1234!', PASSWORD_BCRYPT);
$hashAdmin = password_hash('Admin2026!', PASSWORD_BCRYPT);

$pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'admin', 1)")
    ->execute(['admin@silverhappy.fr', $hashAdmin]);

$id_admin = $pdo->lastInsertId();

echo "Admin cree\n";

$stmtCat = $pdo->prepare("INSERT INTO categories_prestations (nom, description) VALUES (?, ?)");

foreach ($categories as $cat) {
    $stmtCat->execute([$cat, "Categorie " . $cat]);
}

echo "Categories creees\n";

$ids_seniors = [];
$stmtUserSenior = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'senior', 1)");
$stmtSenior = $pdo->prepare("INSERT INTO senior (id_senior, nom, prenom, telephone, date_naissance, adresse) VALUES (?, ?, ?, ?, ?, ?)");

for ($i = 1; $i <= 50; $i++) {
    $prenom = $prenoms[array_rand($prenoms)];
    $nom = $noms[array_rand($noms)];
    $email = strtolower($prenom . $i . '@example.com');
    $tel = '06' . rand(10000000, 99999999);
    $date = date('Y-m-d', strtotime('-' . rand(60, 90) . ' years'));
    $adresse = rand(1, 150) . ' rue Exemple, ' . $villes[array_rand($villes)];

    $stmtUserSenior->execute([$email, $hash]);
    $id = $pdo->lastInsertId();

    $stmtSenior->execute([$id, $nom, $prenom, $tel, $date, $adresse]);
    $ids_seniors[] = $id;
}

echo "Seniors crees\n";

$ids_prestataires = [];
$stmtUserPres = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif) VALUES (?, ?, 'prestataire', 1)");
$stmtPres = $pdo->prepare("INSERT INTO prestataire (id_prestataire, nom, prenom, ville, categorie, description, statut, tarif_horaire) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

for ($i = 1; $i <= 20; $i++) {
    $prenom = $prenoms[array_rand($prenoms)];
    $nom = $noms[array_rand($noms)];
    $email = strtolower('pres' . $i . '@example.com');
    $ville = $villes[array_rand($villes)];
    $cat = $categories[array_rand($categories)];
    $statut = 'valide';
    $tarif = rand(20, 80);

    $stmtUserPres->execute([$email, $hash]);
    $id = $pdo->lastInsertId();

    $stmtPres->execute([$id, $nom, $prenom, $ville, $cat, 'Prestataire Silver Happy', $statut, $tarif]);
    $ids_prestataires[] = $id;
}

echo "Prestataires crees\n";

$stmtRes = $pdo->prepare("INSERT INTO reservation (id_senior, id_prestataire, date_reservation, description, statut) VALUES (?, ?, ?, ?, ?)");
$statutsRes = ['en_attente','confirme','termine','annule'];

for ($i = 0; $i < 100; $i++) {
    $stmtRes->execute([
        $ids_seniors[array_rand($ids_seniors)],
        $ids_prestataires[array_rand($ids_prestataires)],
        date('Y-m-d H:i:s', strtotime('-' . rand(0, 200) . ' days')),
        'Reservation test',
        $statutsRes[array_rand($statutsRes)]
    ]);
}

echo "Reservations creees\n";

$stmtArt = $pdo->prepare("INSERT INTO article (nom, description, prix, categorie, disponible) VALUES (?, ?, ?, ?, 1)");

for ($i = 0; $i < 20; $i++) {
    $nom = $articles_noms[array_rand($articles_noms)];
    $stmtArt->execute([$nom, 'Article pour senior', rand(10, 150), 'Confort']);
}

echo "Articles crees\n";

$articles = $pdo->query("SELECT id_article, nom, prix FROM article")->fetchAll(PDO::FETCH_ASSOC);
$stmtCmd = $pdo->prepare("INSERT INTO commandes (id_senior, id_article, nom_article, prix, statut, created_at) VALUES (?, ?, ?, ?, ?, ?)");

$statutsCmd = ['en_attente','expediee','livree','annulee'];

for ($i = 0; $i < 80; $i++) {
    $article = $articles[array_rand($articles)];

    $stmtCmd->execute([
        $ids_seniors[array_rand($ids_seniors)],
        $article['id_article'],
        $article['nom'],
        $article['prix'],
        $statutsCmd[array_rand($statutsCmd)],
        date('Y-m-d H:i:s')
    ]);
}

echo "Commandes creees\n";

$stmtEvt = $pdo->prepare("INSERT INTO evenements (titre, date_debut, lieu, nombre_places) VALUES (?, ?, ?, ?)");

for ($i = 1; $i <= 15; $i++) {
    $stmtEvt->execute([
        'Evenement ' . $i,
        date('Y-m-d H:i:s', strtotime('+' . rand(1, 60) . ' days')),
        $villes[array_rand($villes)],
        rand(10, 40)
    ]);
}

echo "Evenements crees\n";

$stmtConseil = $pdo->prepare("INSERT INTO conseils (titre, contenu, categorie) VALUES (?, ?, ?)");

for ($i = 1; $i <= 15; $i++) {
    $stmtConseil->execute([
        'Conseil ' . $i,
        'Contenu du conseil ' . $i,
        $categories[array_rand($categories)]
    ]);
}

echo "Conseils crees\n";

$stmtMsg = $pdo->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, lu) VALUES (?, ?, ?, ?)");

for ($i = 0; $i < 100; $i++) {
    $stmtMsg->execute([
        $ids_seniors[array_rand($ids_seniors)],
        $id_admin,
        $messages[array_rand($messages)],
        rand(0, 1)
    ]);
}

echo "Messages crees\n";

$stmtDevis = $pdo->prepare("INSERT INTO devis (id_prestataire, id_senior, montant, description, statut) VALUES (?, ?, ?, ?, ?)");

for ($i = 0; $i < 50; $i++) {
    $stmtDevis->execute([
        $ids_prestataires[array_rand($ids_prestataires)],
        $ids_seniors[array_rand($ids_seniors)],
        rand(30, 200),
        'Devis test',
        'en_attente'
    ]);
}

echo "Devis crees\n";

$stmtRdv = $pdo->prepare("INSERT INTO rdv_medicaux (id_senior, id_medecin, date_rdv, statut, notes) VALUES (?, ?, ?, ?, ?)");

for ($i = 0; $i < 50; $i++) {
    $stmtRdv->execute([
        $ids_seniors[array_rand($ids_seniors)],
        $ids_prestataires[array_rand($ids_prestataires)],
        date('Y-m-d H:i:s', strtotime(rand(-30, 30) . ' days')),
        'en_attente',
        'RDV test'
    ]);
}

echo "RDV medicaux crees\n";

echo "\nSeeder termine\n";
echo "Compte admin : admin@silverhappy.fr / Admin2026!\n";
echo "Compte test : jean1@example.com / Test1234!\n";
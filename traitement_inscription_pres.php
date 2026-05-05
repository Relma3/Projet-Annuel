<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscriptionpres.php');
    exit();
}

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
 
}

$prenom        = htmlspecialchars(trim($_POST['prenom']        ?? ''));
$nom           = htmlspecialchars(trim($_POST['nom']           ?? ''));
$email         = htmlspecialchars(trim($_POST['email']         ?? ''));
$date_naissance= $_POST['date_naissance'] ?? '';
$telephone     = htmlspecialchars(trim($_POST['telephone']     ?? ''));
$adresse       = htmlspecialchars(trim($_POST['adresse']       ?? ''));
$ville         = htmlspecialchars(trim($_POST['ville']         ?? ''));
$siret         = htmlspecialchars(trim($_POST['siret']         ?? ''));
$raison_sociale= htmlspecialchars(trim($_POST['raison_sociale']?? ''));
$categorie     = htmlspecialchars(trim($_POST['categorie']     ?? ''));
$tarif_horaire = floatval($_POST['tarif_horaire'] ?? 0);
$bio           = htmlspecialchars(trim($_POST['bio']           ?? ''));
$password      = $_POST['password'] ?? '';

if (!$prenom || !$nom || !$email || !$password || !$categorie || !$siret) {
    header('Location: inscriptionpres.php?error=champs_manquants');
    exit();
}

if (strlen($password) < 8) {
    header('Location: inscriptionpres.php?error=password_court');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: inscriptionpres.php?error=email_invalide');
    exit();
}

if ($date_naissance) {
    $age = floor((time() - strtotime($date_naissance)) / (365.25 * 24 * 3600));
    if ($age < 18) {
        header('Location: inscriptionpres.php?error=age_invalide');
        exit();
    }
}

$uploadDir = __DIR__ . '/uploads/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function uploadDocument(string $fieldName, string $uploadDir, int $idUser): ?array {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName  = $fieldName . '_' . $idUser . '_' . time() . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return null;
    }

    return [
        'nom_original'  => $file['name'],
        'chemin_fichier'=> 'uploads/documents/' . $newName,
        'taille_octets' => $file['size'],
        'mime_type'     => $mimeType,
    ];
}

try {
    $pdo->beginTransaction();

    $stmtCheck = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        $pdo->rollBack();
        header('Location: inscriptionpres.php?error=email_existe');
        exit();
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmtUser = $pdo->prepare("
        INSERT INTO utilisateur (email, mot_de_passe, type_utilisateur, est_actif)
VALUES (?, ?, 'prestataire', 1)
    ");
    $stmtUser->execute([$email, $hash]);
    $idUser = $pdo->lastInsertId();


   $stmtPres = $pdo->prepare("
    INSERT INTO prestataire (
        id_prestataire, nom, prenom, date_naissance, telephone, adresse,
        ville, categorie, siret, raison_sociale, bio, tarif_horaire, statut
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
");
    $stmtPres->execute([
        $idUser, $nom, $prenom, $date_naissance ?: null, $telephone,
        $adresse, $ville, $categorie, $siret, $raison_sociale ?: null,
        $bio ?: null, $tarif_horaire ?: null
    ]);

    $documents = [
        'casier_judiciaire' => 'casier_judiciaire',
        'diplome'           => 'diplome',
        'lettre_reco'       => 'reco',
        'piece_identite'    => 'piece_identite',
    ];

    foreach ($documents as $fieldName => $typeDoc) {
        $doc = uploadDocument($fieldName, $uploadDir, $idUser);
        if ($doc) {
            $stmtDoc = $pdo->prepare("
                INSERT INTO documents_presta (id_prestataire, type_document, nom_original, chemin_fichier, taille_octets, mime_type, statut)
                VALUES (?, ?, ?, ?, ?, ?, 'en_attente')
            ");
            $stmtDoc->execute([
                $idUser,
                $typeDoc,
                $doc['nom_original'],
                $doc['chemin_fichier'],
                $doc['taille_octets'],
                $doc['mime_type'],
            ]);
        }
    }

    $pdo->commit();

    header('Location: connexionpres.php?inscrit=1');
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    
    error_log("Erreur inscription prestataire : " . $e->getMessage());
    header('Location: inscriptionpres.php?error=sql');
    exit();
}

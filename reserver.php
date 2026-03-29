<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_service = isset($_GET['id_service']) ? intval($_GET['id_service']) : 0;
$id_senior = $_SESSION['id'];

$stmt = $pdo->prepare("
    SELECT s.*, p.prenom, p.nom, p.id_prestataire 
    FROM services s
    JOIN prestataire p ON s.id_prestataire = p.id_prestataire
    WHERE s.id_service = ?
");
$stmt->execute([$id_service]);
$offre = $stmt->fetch();

if (!$offre) { die("Service introuvable."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date_res'] . ' ' . $_POST['heure_res'] . ':00';
    $id_pres = $offre['id_prestataire'];

    try {

        $ins = $pdo->prepare("INSERT INTO reservation (id_senior, id_prestataire, id_service, date_reservation, statut) VALUES (?, ?, ?, ?, 'en_attente')");
        $ins->execute([$id_senior, $id_pres, $id_service, $date]);
        
        header('Location: dashboardS.php?msg=ok#planning');
        exit();
    } catch (PDOException $e) { 
        $erreur = "Erreur SQL : " . $e->getMessage(); 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver un service — Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F4EDDE] font-sans text-slate-800">

    <main class="min-h-screen flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-lg rounded-[30px] shadow-2xl overflow-hidden">
            
            <div class="bg-[#FF885B] p-8 text-white text-center">
                <h1 class="font-title text-2xl font-bold">Réserver votre prestation</h1>
                <p class="opacity-90 mt-2"><?php echo htmlspecialchars($offre['nom_service']); ?> avec <?php echo htmlspecialchars($offre['prenom']); ?></p>
            </div>

            <form method="POST" class="p-8 space-y-6">
                <?php if(isset($erreur)): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-bold"><?php echo $erreur; ?></div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2 ml-2">Choisir le jour</label>
                        <input type="date" name="date_res" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-[#FF885B] outline-none font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2 ml-2">Choisir l'heure</label>
                        <input type="time" name="heure_res" required class="w-full p-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-[#FF885B] outline-none font-bold">
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Tarif du service :</span>
                    <span class="text-2xl font-bold text-slate-900"><?php echo $offre['prix']; ?> €</span>
                </div>

                <div class="flex gap-4">
                    <a href="liste_prestataires.php" class="flex-1 text-center py-4 text-slate-400 font-bold hover:text-slate-600 transition-colors">Annuler</a>
                    <button type="submit" class="flex-[2] bg-[#FF885B] text-white py-4 rounded-2xl font-bold shadow-lg shadow-orange-200 hover:scale-105 transition-transform">
                        Confirmer le RDV
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>

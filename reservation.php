<?php
session_start();
require_once 'db_connect.php';
require_once 'check_session.php';

if(!isset($_SESSION['id']) || $_SESSION['type'] !== 'senior') {
    header('Location: connexion.php');
    exit();
}

$id_pres = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id_pres === 0) {
    header('Location: services.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM prestataire WHERE id_prestataire = ? AND statut = 'valide'");
$stmt->execute([$id_pres]);
$pres = $stmt->fetch();

if(!$pres) {
    die("Prestataire introuvable ou non validé.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver avec <?php echo htmlspecialchars($pres['prenom']); ?> - Silver Happy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/168ebc7feb.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sable-doux': '#F4EDDE', 'orange-corail': '#FF885B', 'vert-menthe': '#A0E8AF', 'peche-pastel': '#FFD9CA' },
                    fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'title': ['Quicksand', 'sans-serif'] },
                    borderRadius: { 'senior': '28px' }
                }
            }
        }
    </script>
</head>
<body class="bg-sable-doux font-sans">
    <?php include 'accessibilite.php'; ?>

    <nav class="fixed w-full bg-white/90 backdrop-blur-md shadow-sm z-50 px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2">
            <img src="logo.png" alt="Logo" class="h-10">
            <span class="text-xl font-bold text-orange-corail font-title">Silver Happy</span>
        </a>
        <a href="prestation_details.php" class="text-slate-500 font-bold hover:text-orange-corail"><i class="fa-solid fa-xmark mr-2"></i> Annuler</a>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-4xl mx-auto">
        <div class="bg-white rounded-senior shadow-xl overflow-hidden border border-slate-100">
            
            <div class="bg-peche-pastel p-8 flex items-center gap-6 border-b border-orange-100">
                <div class="w-20 h-20 bg-white rounded-full overflow-hidden border-4 border-white shadow-sm">
                    <img src="perso.png" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Réserver une prestation</h1>
                    <p class="text-orange-corail font-bold"><i class="fa-solid fa-user-check mr-2"></i> Avec <?php echo htmlspecialchars($pres['prenom'] . ' ' . $pres['nom']); ?></p>
                </div>
            </div>

            <form action="traitement_reservation.php" method="POST" class="p-8 md:p-12 space-y-10">
                <input type="hidden" name="id_prestataire" value="<?php echo $id_pres; ?>">

                <div class="space-y-4">
                    <h2 class="text-xl font-bold flex items-center gap-3 text-slate-800">
                        <span class="bg-orange-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                        Choisir une date
                    </h2>
                    <input type="date" name="date_rdv" required min="<?php echo date('Y-m-d'); ?>" 
                           class="w-full md:w-1/2 px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-orange-corail outline-none text-lg">
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-bold flex items-center gap-3 text-slate-800">
                        <span class="bg-orange-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                        Choisir l'heure
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php 
                        $heures = ["09:00", "10:00", "11:00", "14:00", "15:00", "16:00", "17:00", "18:00"];
                        foreach($heures as $h): ?>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="heure_rdv" value="<?php echo $h; ?>" required class="peer sr-only">
                            <div class="p-4 text-center border-2 border-slate-100 rounded-2xl bg-slate-50 peer-checked:bg-orange-corail peer-checked:border-orange-corail peer-checked:text-white transition-all hover:bg-peche-pastel">
                                <span class="font-bold"><?php echo $h; ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-bold flex items-center gap-3 text-slate-800">
                        <span class="bg-orange-corail text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                        Message pour le prestataire (optionnel)
                    </h2>
                    <textarea name="commentaire" placeholder="Ex: Précisions sur l'adresse ou besoins spécifiques..." 
                              class="w-full p-6 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-orange-corail outline-none min-h-[120px]"></textarea>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-orange-corail text-white py-5 rounded-senior font-bold text-xl shadow-lg shadow-orange-corail/30 hover:scale-[1.02] transition-all">
                        Confirmer la demande de rendez-vous
                    </button>
                    <p class="text-center text-slate-400 mt-4 text-sm">
                        <i class="fa-solid fa-lock mr-2"></i> Paiement sécurisé Silver Happy
                    </p>
                </div>
            </form>
        </div>
    </main>

</body>
</html>

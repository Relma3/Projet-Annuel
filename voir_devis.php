<?php
session_start();
require_once 'db_connect.php';


if (!isset($_SESSION['id'])) {
    die("Accès non autorisé. Veuillez vous connecter.");
}

$id_devis = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_devis === 0) {
    die("Numéro de devis invalide.");
}

try {
    $stmt = $pdo->prepare("
        SELECT d.*,
               p.nom AS pres_nom, p.prenom AS pres_prenom, p.adresse AS pres_adresse, p.ville AS pres_ville, p.siret, p.telephone AS pres_tel, up.email AS pres_email,
               s.nom AS sen_nom, s.prenom AS sen_prenom, s.adresse AS sen_adresse, us.email AS sen_email
        FROM devis d
        JOIN prestataire p ON d.id_prestataire = p.id_prestataire
        JOIN utilisateur up ON p.id_prestataire = up.id_utilisateur
        JOIN senior s ON d.id_senior = s.id_senior
        JOIN utilisateur us ON s.id_senior = us.id_utilisateur
        WHERE d.id_devis = ?
    ");
    $stmt->execute([$id_devis]);
    $devis = $stmt->fetch();

    if (!$devis) {
        die("Devis introuvable.");
    }


    if (($_SESSION['type'] === 'senior' && $devis['id_senior'] != $_SESSION['id']) ||
        ($_SESSION['type'] === 'prestataire' && $devis['id_prestataire'] != $_SESSION['id']) && $_SESSION['type'] !== 'admin') {
        die("Accès refusé. Ce devis ne vous appartient pas.");
    }

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis <?php echo htmlspecialchars($devis['numero_devis']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
 
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .shadow-lg { shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 md:p-10 font-sans text-slate-800">

    <div class="max-w-4xl mx-auto bg-white p-10 md:p-16 rounded-2xl shadow-lg border border-slate-200">

      
        <div class="no-print flex justify-between mb-10 pb-6 border-b border-slate-100">
            <button onclick="window.close()" class="text-slate-500 hover:text-slate-800 font-bold transition-colors">
                &larr; Fermer
            </button>
            <button onclick="window.print()" class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition-colors shadow-md">
                🖨️ Imprimer / PDF
            </button>
        </div>

        <div class="flex justify-between items-start mb-16">
            <div>
                <h1 class="text-4xl font-bold text-slate-800 mb-2">DEVIS</h1>
                <p class="text-slate-500 font-bold text-lg">N° <?php echo htmlspecialchars($devis['numero_devis']); ?></p>
                <p class="text-slate-500 text-sm mt-3">Date d'émission : <?php echo date('d/m/Y', strtotime($devis['created_at'])); ?></p>
                <p class="text-red-500 text-sm font-bold mt-1">Valable jusqu'au : <?php echo date('d/m/Y', strtotime($devis['date_validite'])); ?></p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-emerald-600 mb-1" style="font-family: 'Quicksand', sans-serif;">Silver Happy</div>
                <p class="text-sm text-slate-400 font-medium tracking-wide">Mise en relation Senior & Pro</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between mb-16 gap-8 md:gap-0">
       
            <div class="w-full md:w-1/2 pr-0 md:pr-4">
                <h3 class="font-bold text-slate-400 uppercase text-xs tracking-widest mb-4">Émetteur (Prestataire)</h3>
                <p class="font-bold text-xl text-slate-800"><?php echo htmlspecialchars($devis['pres_prenom'] . ' ' . $devis['pres_nom']); ?></p>
                <p class="text-slate-600 mt-2"><?php echo htmlspecialchars($devis['pres_adresse'] ?? 'Adresse non renseignée'); ?></p>
                <p class="text-slate-600"><?php echo htmlspecialchars($devis['pres_ville'] ?? ''); ?></p>
                <p class="text-slate-600 mt-3">✉️ <?php echo htmlspecialchars($devis['pres_email']); ?></p>
                <p class="text-slate-600">📞 <?php echo htmlspecialchars($devis['pres_tel'] ?? 'Non renseigné'); ?></p>
                <p class="text-slate-400 text-sm mt-3 font-medium">SIRET : <?php echo htmlspecialchars($devis['siret'] ?? 'Non renseigné'); ?></p>
            </div>

           
            <div class="w-full md:w-1/2 md:pl-8 md:border-l border-slate-100">
                <h3 class="font-bold text-slate-400 uppercase text-xs tracking-widest mb-4">Client (Senior)</h3>
                <p class="font-bold text-xl text-slate-800"><?php echo htmlspecialchars($devis['sen_prenom'] . ' ' . $devis['sen_nom']); ?></p>
                <p class="text-slate-600 mt-2"><?php echo htmlspecialchars($devis['sen_adresse'] ?? 'Adresse non renseignée'); ?></p>
                <p class="text-slate-600 mt-3">✉️ <?php echo htmlspecialchars($devis['sen_email']); ?></p>
            </div>
        </div>

        <div class="mb-16">
            <h3 class="font-bold text-slate-800 text-lg mb-4 border-b border-slate-200 pb-3">Détail de la prestation</h3>
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <h4 class="font-bold text-emerald-800 text-lg mb-3"><?php echo htmlspecialchars($devis['titre']); ?></h4>
                <p class="text-slate-600 whitespace-pre-line leading-relaxed"><?php echo htmlspecialchars($devis['description']); ?></p>
            </div>
        </div>

  
        <div class="flex justify-end">
            <div class="w-full sm:w-1/2 md:w-1/3 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <div class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Total HT</span>
                    <span class="font-bold text-slate-800"><?php echo number_format($devis['montant_ht'], 2, ',', ' '); ?> €</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">TVA (<?php echo floatval($devis['tva_taux']); ?>%)</span>
                    <span class="font-bold text-slate-800"><?php echo number_format($devis['montant_ttc'] - $devis['montant_ht'], 2, ',', ' '); ?> €</span>
                </div>
                <div class="flex justify-between py-4 text-xl">
                    <span class="font-bold text-slate-800">Total TTC</span>
                    <span class="font-bold text-emerald-600"><?php echo number_format($devis['montant_ttc'], 2, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>

        <div class="mt-16 pt-8 border-t border-slate-200 text-center text-sm text-slate-400">
            <p>Devis généré automatiquement via la plateforme Silver Happy.</p>
            <p class="mt-1 font-medium">L'acceptation de ce devis sur votre espace client vaut pour accord et engage au paiement de la prestation.</p>
        </div>

    </div>

</body>
</html>

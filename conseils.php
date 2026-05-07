<?php
require_once 'db_connect.php';

$stmt = $pdo->query("SELECT * FROM conseil WHERE visible = 1 ORDER BY created_at DESC");
$conseils = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conseils — Silver Happy</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        corail: '#E37A55',
                        sable: '#F4EDDE',
                        'peche-pale': '#FDF0EB'
                    },
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        title: ['Quicksand', 'sans-serif']
                    },
                    borderRadius: {
                        senior: '28px'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-sable font-sans">

<!-- NAV -->
<div class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-title font-bold text-corail">Conseils</h1>

    <!-- BOUTON RETOUR -->
    <a href="javascript:history.back()" 
       class="bg-corail text-white px-5 py-2 rounded-xl font-bold hover:scale-105 transition">
        ← Retour
    </a>
</div>

<!-- CONTENU -->
<div class="max-w-6xl mx-auto py-10 px-6">

    <?php if (empty($conseils)): ?>
        <div class="bg-white p-10 rounded-senior text-center text-gray-400 italic shadow">
            Aucun conseil disponible pour le moment.
        </div>
    <?php else: ?>

        <div class="grid md:grid-cols-2 gap-6">

            <?php foreach ($conseils as $c): ?>
                <div class="bg-white p-6 rounded-senior shadow hover:shadow-lg transition border-l-8 border-corail">

                    <!-- CATEGORIE -->
                    <span class="text-xs font-bold text-gray-400 uppercase">
                        <?= htmlspecialchars($c['categorie'] ?? 'Général') ?>
                    </span>

                    <!-- TITRE -->
                    <h2 class="text-xl font-bold mt-2 text-gray-800">
                        <?= htmlspecialchars($c['titre']) ?>
                    </h2>

                    <!-- CONTENU -->
                    <p class="text-gray-600 mt-3 leading-relaxed">
                        <?= nl2br(htmlspecialchars($c['contenu'])) ?>
                    </p>

                    <!-- AUTEUR -->
                    <?php if (!empty($c['auteur'])): ?>
                        <p class="text-xs text-gray-400 mt-4 italic">
                            Par <?= htmlspecialchars($c['auteur']) ?>
                        </p>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'prestataire') {
    header('Location: connexion.php');
    exit();
}

$id_res = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['a']) ? $_GET['a'] : '';

if ($id_res > 0 && !empty($action)) {
    $nouveau_statut = match($action) {
        'accepter' => 'confirme',
        'refuser'  => 'annule',
        'terminer' => 'termine',
        default    => ''
    };

    if (!empty($nouveau_statut)) {
        try {
            $pdo->beginTransaction();


            $stmtGet = $pdo->prepare("
                SELECT r.*, s.prix AS service_prix, s.nom_service, s.id_service
                FROM reservation r
                LEFT JOIN disponibilites d ON r.id_disponibilite = d.id_disponibilite
                LEFT JOIN services s ON d.id_service = s.id_service
                WHERE r.id_reservation = ? AND r.id_prestataire = ?
            ");
            $stmtGet->execute([$id_res, $_SESSION['id']]);
            $reservation = $stmtGet->fetch();

            if ($reservation) {

                
                $pdo->prepare("
                    UPDATE reservation SET statut = ? WHERE id_reservation = ?
                ")->execute([$nouveau_statut, $id_res]);

                if ($nouveau_statut === 'confirme') {

                    if ($reservation['id_disponibilite']) {
                        $stmtDispo = $pdo->prepare("SELECT * FROM disponibilites WHERE id_disponibilite = ?");
                        $stmtDispo->execute([$reservation['id_disponibilite']]);
                        $dispo = $stmtDispo->fetch();

                        if ($dispo) {
                            $pdo->prepare("
                                UPDATE disponibilites SET type = 'reserve', id_reservation = ?
                                WHERE id_disponibilite = ?
                            ")->execute([$id_res, $dispo['id_disponibilite']]);

                            if ($dispo['date_debut'] < $reservation['date_reservation']) {
                                $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")
                                    ->execute([$reservation['id_prestataire'], $dispo['date_debut'], $reservation['date_reservation']]);
                            }
                            if ($dispo['date_fin'] > $reservation['date_fin']) {
                                $pdo->prepare("INSERT INTO disponibilites (id_prestataire, date_debut, date_fin, type) VALUES (?, ?, ?, 'libre')")
                                    ->execute([$reservation['id_prestataire'], $reservation['date_fin'], $dispo['date_fin']]);
                            }
                        }
                    }

                    $debut   = strtotime($reservation['date_reservation']);
                    $fin     = strtotime($reservation['date_fin']);
                    $dureeH  = ($fin - $debut) / 3600;

                    
                    $prixHeure = (float)($reservation['service_prix'] ?? 0);
                    if ($prixHeure <= 0) {
                        $stmtTarif = $pdo->prepare("SELECT tarif_horaire FROM prestataire WHERE id_prestataire = ?");
                        $stmtTarif->execute([$reservation['id_prestataire']]);
                        $prixHeure = (float)($stmtTarif->fetchColumn() ?? 0);
                    }

                    $montantHT  = round($prixHeure * $dureeH, 2);
                    $tvaTaux    = 20.00;
                    $montantTTC = round($montantHT * (1 + $tvaTaux / 100), 2);
                    $commission = round($montantTTC * 0.01, 2);

                
                    $numeroDevis = 'DEV-' . date('Y-m') . '-' . str_pad($id_res, 4, '0', STR_PAD_LEFT);

 
                    $titreDevis = $reservation['nom_service']
                        ? htmlspecialchars($reservation['nom_service']) . ' — ' . date('d/m/Y', $debut)
                        : 'Prestation du ' . date('d/m/Y', $debut);

                    $descDevis = 'Prestation du ' . date('d/m/Y à H:i', $debut)
                        . ' au ' . date('d/m/Y à H:i', $fin)
                        . ' (' . round($dureeH, 1) . 'h × ' . $prixHeure . '€/h)';

                    $dateValidite = date('Y-m-d H:i:s', strtotime($reservation['date_reservation']) - 12 * 3600);

                    $pdo->prepare("
                        INSERT INTO devis (
                            numero_devis, id_prestataire, id_senior, id_reservation,
                            titre, description, montant_ht, tva_taux, montant_ttc,
                            statut, date_validite
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'envoye', ?)
                        ON DUPLICATE KEY UPDATE statut = 'envoye'
                    ")->execute([
                        $numeroDevis,
                        $reservation['id_prestataire'],
                        $reservation['id_senior'],
                        $id_res,
                        $titreDevis,
                        $descDevis,
                        $montantHT,
                        $tvaTaux,
                        $montantTTC,
                        $dateValidite
                    ]);

                    $pdo->prepare("
                      UPDATE reservation 
                      SET commission_sh = ? 
                      WHERE id_reservation = ?
                    ")->execute([$commission, $id_res]);
                }

     
                if ($nouveau_statut === 'annule' && $reservation['id_disponibilite']) {
                    $pdo->prepare("
                        UPDATE disponibilites 
                        SET type = 'libre', id_reservation = NULL 
                        WHERE id_reservation = ? AND type = 'reserve'
                    ")->execute([$id_res]);
                }
            }

            $pdo->commit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            die("Erreur : " . $e->getMessage());
        }
    }
}

header('Location: dashboardP.php#planning');
exit();

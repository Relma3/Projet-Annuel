<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    private function nouveauPdf(): Dompdf
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        return new Dompdf($options);
    }

    private function debutHtml(string $titre, string $numero): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 13px;
                    color: #333;
                    margin: 0;
                    padding: 30px;
                }

                .haut {
                    display: flex;
                    justify-content: space-between;
                    border-bottom: 2px solid #E07820;
                    padding-bottom: 15px;
                    margin-bottom: 25px;
                }

                .logo {
                    color: #E07820;
                    font-size: 22px;
                    font-weight: bold;
                }

                .logo small {
                    display: block;
                    font-size: 10px;
                    color: #999;
                    font-weight: normal;
                }

                h2 {
                    color: #E07820;
                    font-size: 16px;
                    margin: 0 0 5px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }

                th {
                    background: #E07820;
                    color: white;
                    padding: 8px 12px;
                    text-align: left;
                    font-size: 12px;
                }

                td {
                    padding: 8px 12px;
                    border-bottom: 1px solid #eee;
                    font-size: 12px;
                }

                tr:nth-child(even) td {
                    background: #fafafa;
                }

                .total td {
                    font-weight: bold;
                    background: #FEF3E7;
                    border-top: 2px solid #E07820;
                }

                .badge {
                    display: inline-block;
                    background: #E07820;
                    color: white;
                    padding: 3px 10px;
                    border-radius: 12px;
                    font-size: 11px;
                }

                .bas {
                    margin-top: 40px;
                    font-size: 10px;
                    color: #aaa;
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="haut">
                <div>
                    <div class="logo">
                        Silver Happy
                        <small>Bien vivre après 60 ans</small>
                    </div>

                    <div style="margin-top:6px;font-size:11px;color:#777">
                        244 rue du Faubourg Saint Antoine, 75011 Paris<br>
                        contact@silverhappy.fr
                    </div>
                </div>

                <div style="text-align:right">
                    <h2>' . htmlspecialchars($titre) . '</h2>
                    <div><span class="badge">N° ' . htmlspecialchars($numero) . '</span></div>
                    <div style="margin-top:6px;font-size:11px;color:#777">
                        Date : ' . date('d/m/Y') . '
                    </div>
                </div>
            </div>';
    }

    private function finHtml(): string
    {
        return '
            <div class="bas">
                Silver Happy SAS — SIRET 000 000 000 00000 — TVA FR00000000000<br>
                Document généré automatiquement le ' . date('d/m/Y à H:i') . '
            </div>
        </body>
        </html>';
    }

    private function enregistrerPdf(string $html, string $dossier, string $numero): string
    {
        if (!is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }

        $pdf = $this->nouveauPdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $chemin = $dossier . $numero . '.pdf';
        file_put_contents($chemin, $pdf->output());

        return $chemin;
    }

    public function genererFactureSenior(PDO $pdo, int $id_paiement): string
    {
        $stmt = $pdo->prepare("
            SELECT p.*, u.email, s.nom, s.prenom, s.adresse, s.ville
            FROM paiements p
            JOIN utilisateur u ON u.id_utilisateur = p.id_payeur
            JOIN senior s ON s.id_senior = p.id_payeur
            WHERE p.id_paiement = ?
        ");
        $stmt->execute([$id_paiement]);
        $paiement = $stmt->fetch();

        if (!$paiement) {
            throw new Exception("Paiement introuvable : " . $id_paiement);
        }

        $numero = 'SH-S-' . date('Ymd') . '-' . str_pad($id_paiement, 5, '0', STR_PAD_LEFT);
        $montantTtc = $paiement['montant_cents'] / 100;
        $montantHt = $montantTtc / 1.2;

        $nomClient = htmlspecialchars($paiement['prenom'] . ' ' . $paiement['nom']);
        $emailClient = htmlspecialchars($paiement['email']);
        $adresseClient = htmlspecialchars(($paiement['adresse'] ?? '') . ' ' . ($paiement['ville'] ?? ''));

        $libelle = match ($paiement['type_objet']) {
            'abonnement'  => 'Abonnement Silver Happy',
            'reservation' => 'Prestation de service',
            'devis'       => 'Devis accepté',
            default       => 'Achat Silver Happy'
        };

        $html = $this->debutHtml('Facture', $numero);

        $html .= '
            <div style="margin-bottom:20px">
                <strong>Facturé à :</strong><br>
                ' . $nomClient . '<br>
                ' . $emailClient . '<br>
                ' . $adresseClient . '
            </div>

            <table>
                <tr>
                    <th>Description</th>
                    <th>Montant HT</th>
                    <th>TVA</th>
                    <th>Montant TTC</th>
                </tr>

                <tr>
                    <td>' . htmlspecialchars($libelle) . '</td>
                    <td>' . number_format($montantHt, 2, ',', ' ') . ' €</td>
                    <td>20%</td>
                    <td>' . number_format($montantTtc, 2, ',', ' ') . ' €</td>
                </tr>

                <tr class="total">
                    <td colspan="3">Total TTC</td>
                    <td>' . number_format($montantTtc, 2, ',', ' ') . ' €</td>
                </tr>
            </table>

            <p style="margin-top:20px;font-size:11px;color:#777">
                Paiement effectué le ' . date('d/m/Y', strtotime($paiement['date_paiement'] ?? 'now')) . '
                via Stripe. Référence : ' . htmlspecialchars($paiement['stripe_payment_intent_id'] ?? 'N/A') . '
            </p>';

        $html .= $this->finHtml();

        $chemin = $this->enregistrerPdf(
            $html,
            __DIR__ . '/factures/seniors/',
            $numero
        );

        $stmt = $pdo->prepare("
            INSERT INTO factures (
                numero_facture,
                id_prestataire,
                mois,
                annee,
                montant_brut_cents,
                montant_net_cents,
                statut,
                pdf_path,
                id_paiement
            )
            VALUES (?, 0, ?, ?, ?, ?, 'generee', ?, ?)
            ON DUPLICATE KEY UPDATE pdf_path = VALUES(pdf_path)
        ");

        $stmt->execute([
            $numero,
            date('n'),
            date('Y'),
            $paiement['montant_cents'],
            $paiement['montant_cents'],
            'factures/seniors/' . $numero . '.pdf',
            $id_paiement
        ]);

        return $chemin;
    }

    public function genererFacturePrestataire(PDO $pdo, int $id_prestataire, int $mois, int $annee): string
    {
        $stmt = $pdo->prepare("
            SELECT p.*, u.email
            FROM prestataire p
            JOIN utilisateur u ON u.id_utilisateur = p.id_prestataire
            WHERE p.id_prestataire = ?
        ");
        $stmt->execute([$id_prestataire]);
        $presta = $stmt->fetch();

        if (!$presta) {
            throw new Exception("Prestataire introuvable : " . $id_prestataire);
        }

        $stmt = $pdo->prepare("
            SELECT r.*, s.nom AS senior_nom, s.prenom AS senior_prenom,
                   srv.nom_service, srv.tarif_horaire
            FROM reservation r
            JOIN senior s ON s.id_senior = r.id_senior
            LEFT JOIN services srv ON srv.id_prestataire = r.id_prestataire
            WHERE r.id_prestataire = ?
              AND r.statut = 'termine'
              AND MONTH(r.date_reservation) = ?
              AND YEAR(r.date_reservation) = ?
            ORDER BY r.date_reservation
        ");
        $stmt->execute([$id_prestataire, $mois, $annee]);
        $reservations = $stmt->fetchAll();

        $montantBrut = 0;
        $tauxCommission = 15;
        $lignes = '';

        foreach ($reservations as $reservation) {
            $tarif = (float) ($reservation['tarif_horaire'] ?? 30);
            $montantBrut += $tarif;

            $lignes .= '
                <tr>
                    <td>' . date('d/m/Y', strtotime($reservation['date_reservation'])) . '</td>
                    <td>' . htmlspecialchars($reservation['senior_prenom'] . ' ' . $reservation['senior_nom']) . '</td>
                    <td>' . htmlspecialchars($reservation['nom_service'] ?? 'Prestation') . '</td>
                    <td>' . number_format($tarif, 2, ',', ' ') . ' €</td>
                </tr>';
        }

        if ($lignes === '') {
            $lignes = '
                <tr>
                    <td colspan="4" style="text-align:center;color:#aaa">
                        Aucune prestation ce mois
                    </td>
                </tr>';
        }

        $commission = $montantBrut * $tauxCommission / 100;
        $montantNet = $montantBrut - $commission;

        $numero = 'SH-P-' . $annee . str_pad($mois, 2, '0', STR_PAD_LEFT)
            . '-' . str_pad($id_prestataire, 4, '0', STR_PAD_LEFT);

        $moisLabel = strftime('%B %Y', mktime(0, 0, 0, $mois, 1, $annee));

        $html = $this->debutHtml('Relevé mensuel prestataire', $numero);

        $html .= '
            <div style="display:flex;justify-content:space-between;margin-bottom:20px">
                <div>
                    <strong>Prestataire :</strong><br>
                    ' . htmlspecialchars($presta['prenom'] . ' ' . $presta['nom']) . '<br>
                    ' . htmlspecialchars($presta['email']) . '<br>
                    SIRET : ' . htmlspecialchars($presta['siret'] ?? 'N/A') . '
                </div>

                <div style="text-align:right">
                    <strong>Période :</strong><br>
                    ' . $moisLabel . '<br>
                    <strong>Prestations :</strong> ' . count($reservations) . '
                </div>
            </div>

            <table>
                <tr>
                    <th>Date</th>
                    <th>Adhérent</th>
                    <th>Service</th>
                    <th>Montant</th>
                </tr>

                ' . $lignes . '

                <tr class="total">
                    <td colspan="3">Montant brut</td>
                    <td>' . number_format($montantBrut, 2, ',', ' ') . ' €</td>
                </tr>

                <tr>
                    <td colspan="3">Commission Silver Happy (' . $tauxCommission . '%)</td>
                    <td style="color:#c0392b">
                        - ' . number_format($commission, 2, ',', ' ') . ' €
                    </td>
                </tr>

                <tr class="total">
                    <td colspan="3">Net à recevoir</td>
                    <td style="color:#27ae60">
                        ' . number_format($montantNet, 2, ',', ' ') . ' €
                    </td>
                </tr>
            </table>

            <p style="margin-top:20px;font-size:11px;color:#777">
                Virement sous 5 jours ouvrés sur le compte IBAN :
                <strong>' . htmlspecialchars(substr($presta['iban'] ?? 'FRXX...', 0, 4) . '...') . '</strong>
            </p>';

        $html .= $this->finHtml();

        $chemin = $this->enregistrerPdf(
            $html,
            __DIR__ . '/factures/prestataires/',
            $numero
        );

        $stmt = $pdo->prepare("
            INSERT INTO factures (
                numero_facture,
                id_prestataire,
                mois,
                annee,
                nb_prestations,
                montant_brut_cents,
                taux_commission,
                commission_sh_cents,
                montant_net_cents,
                statut,
                pdf_path
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'generee', ?)
            ON DUPLICATE KEY UPDATE
                nb_prestations = VALUES(nb_prestations),
                montant_brut_cents = VALUES(montant_brut_cents),
                montant_net_cents = VALUES(montant_net_cents),
                pdf_path = VALUES(pdf_path),
                statut = 'generee'
        ");

        $stmt->execute([
            $numero,
            $id_prestataire,
            $mois,
            $annee,
            count($reservations),
            (int) ($montantBrut * 100),
            $tauxCommission,
            (int) ($commission * 100),
            (int) ($montantNet * 100),
            'factures/prestataires/' . $numero . '.pdf'
        ]);

        return $chemin;
    }

    public static function servir(string $chemin): void
    {
        if (!file_exists($chemin)) {
            http_response_code(404);
            exit('Fichier introuvable');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($chemin) . '"');
        header('Content-Length: ' . filesize($chemin));

        readfile($chemin);
        exit;
    }
}
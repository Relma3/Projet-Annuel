-- Schema Silver Happy — export phpMyAdmin


-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : lun. 27 avr. 2026 à 08:41
-- Version du serveur : 10.11.16-MariaDB-ubu2204
-- Version de PHP : 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `silver_happy`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

CREATE TABLE `abonnement` (
  `id_abonnement` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `type` enum('mensuel','annuel') NOT NULL DEFAULT 'annuel',
  `statut` enum('actif','expire','annule') NOT NULL DEFAULT 'actif',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `montant` decimal(8,2) NOT NULL DEFAULT 40.00,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `id_paiement` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories_prestations`
--

CREATE TABLE `categories_prestations` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id_commande` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_article` int(11) DEFAULT NULL,
  `nom_article` varchar(200) DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `statut` enum('en_attente','expediee','livree','annulee') DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conseil`
--

CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `auteur` varchar(100) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `id_devis` int(10) UNSIGNED NOT NULL,
  `numero_devis` varchar(30) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_reservation` int(11) DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `montant_ht` decimal(10,2) NOT NULL,
  `tva_taux` decimal(5,2) DEFAULT 20.00,
  `montant_ttc` decimal(10,2) NOT NULL,
  `statut` enum('brouillon','envoye','accepte','refuse','expire') DEFAULT 'brouillon',
  `date_creation` timestamp NULL DEFAULT current_timestamp(),
  `date_validite` date DEFAULT NULL,
  `date_acceptation` datetime DEFAULT NULL,
  `date_refus` datetime DEFAULT NULL,
  `raison_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `disponibilites`
--

CREATE TABLE `disponibilites` (
  `id_disponibilite` int(10) UNSIGNED NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `type` enum('libre','reserve','indisponible') NOT NULL DEFAULT 'libre',
  `id_reservation` int(11) DEFAULT NULL,
  `id_service` int(11) DEFAULT NULL,   
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents_presta`
--

CREATE TABLE `documents_presta` (
  `id_document` int(10) UNSIGNED NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `type_document` enum('casier_judiciaire','diplome','assurance_rc','kbis','piece_identite','rib','reco','autre') NOT NULL,
  `nom_original` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille_octets` bigint(20) UNSIGNED DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `statut` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `date_upload` timestamp NULL DEFAULT current_timestamp(),
  `date_validation` datetime DEFAULT NULL,
  `id_admin_validateur` int(11) DEFAULT NULL,
  `commentaire_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

CREATE TABLE `evaluations` (
  `id_evaluation` int(10) UNSIGNED NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `id_reservation` int(11) NOT NULL,
  `note` tinyint(3) UNSIGNED NOT NULL,
  `commentaire` text DEFAULT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `date_creation` timestamp NULL DEFAULT current_timestamp(),
  `date_moderation` datetime DEFAULT NULL,
  `id_admin_moderateur` int(11) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `date_debut` datetime DEFAULT NULL,
  `lieu` varchar(200) DEFAULT NULL,
  `nombre_places` int(11) DEFAULT 20,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE `factures` (
  `id_facture` int(10) UNSIGNED NOT NULL,
  `numero_facture` varchar(30) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `mois` tinyint(3) UNSIGNED NOT NULL,
  `annee` smallint(5) UNSIGNED NOT NULL,
  `nb_prestations` int(10) UNSIGNED DEFAULT 0,
  `montant_brut_cents` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `taux_commission` decimal(5,2) NOT NULL DEFAULT 15.00,
  `commission_sh_cents` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `montant_net_cents` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `statut` enum('brouillon','generee','envoyee','payee','contestee') DEFAULT 'brouillon',
  `pdf_path` varchar(500) DEFAULT NULL,
  `id_paiement` int(10) UNSIGNED DEFAULT NULL,
  `date_generation` timestamp NULL DEFAULT current_timestamp(),
  `date_envoi` datetime DEFAULT NULL,
  `date_paiement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription_evenement`
--

CREATE TABLE `inscription_evenement` (
  `id_inscription` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_evenement` int(11) NOT NULL,
  `date_inscription` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_contact`
--

CREATE TABLE `message_contact` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('rappel','info','promo','rdv') DEFAULT 'info',
  `lu` tinyint(1) DEFAULT 0,
  `onesignal_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id_paiement` int(10) UNSIGNED NOT NULL,
  `type_objet` enum('abonnement','reservation','devis','facture_presta','autre') NOT NULL,
  `objet_id` int(10) UNSIGNED NOT NULL,
  `id_payeur` int(11) NOT NULL,
  `id_beneficiaire` int(11) DEFAULT NULL,
  `montant_cents` int(10) UNSIGNED NOT NULL,
  `devise` char(3) NOT NULL DEFAULT 'EUR',
  `stripe_payment_intent_id` varchar(100) DEFAULT NULL,
  `stripe_charge_id` varchar(100) DEFAULT NULL,
  `stripe_transfer_id` varchar(100) DEFAULT NULL,
  `stripe_refund_id` varchar(100) DEFAULT NULL,
  `stripe_event_id` varchar(100) DEFAULT NULL,
  `statut` enum('en_attente','reussi','echec','rembourse','annule') NOT NULL DEFAULT 'en_attente',
  `methode` varchar(30) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT current_timestamp(),
  `date_paiement` datetime DEFAULT NULL,
  `date_remboursement` datetime DEFAULT NULL,
  `raison_echec` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prestataire`
--

CREATE TABLE `prestataire` (
  `id_prestataire` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` DATE DEFAULT NULL,        
  `telephone` varchar(20) DEFAULT NULL,     
  `adresse` varchar(255) DEFAULT NULL, 
  `ville` varchar(100) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT 'domicile',
  `description` text DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `raison_sociale` varchar(200) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `note_moyenne` decimal(3,2) DEFAULT 0.00,
  `nombre_evaluations` int(10) UNSIGNED DEFAULT 0,
  `statut` enum('en_attente','valide','suspendu') DEFAULT 'en_attente',
  `date_validation` datetime DEFAULT NULL,
  `id_admin_validateur` int(11) DEFAULT NULL,
  `commentaire_refus` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `tarif_horaire` decimal(8,2) DEFAULT NULL,
  `est_medecin` tinyint(1) DEFAULT 0,
  `specialite` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rdv_medical`
--

CREATE TABLE `rdv_medical` (
  `id_rdv` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `date_rdv` datetime NOT NULL,
  `statut` enum('planifie','confirme','annule','termine') DEFAULT 'planifie',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id_reservation` int(11) NOT NULL,
  `id_senior` int(11) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `date_reservation` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `id_disponibilite` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','confirme','termine','annule') DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `senior`
--

CREATE TABLE `senior` (
  `id_senior` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `tutoriel_vu` tinyint(1) DEFAULT 0,
  `onesignal_player_id` VARCHAR(255) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id_service` int(11) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `nom_service` varchar(255) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_utilisateur` enum('senior','prestataire','admin') NOT NULL,
  `est_actif` tinyint(1) DEFAULT 0,
  `token_confirmation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD PRIMARY KEY (`id_abonnement`),
  ADD KEY `id_senior` (`id_senior`);

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id_article`);

--
-- Index pour la table `categories_prestations`
--
ALTER TABLE `categories_prestations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_senior` (`id_senior`);

--
-- Index pour la table `conseil`
--
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`id_conseil`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id_devis`),
  ADD UNIQUE KEY `uk_numero_devis` (`numero_devis`),
  ADD KEY `idx_presta` (`id_prestataire`),
  ADD KEY `idx_senior` (`id_senior`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `devis_fk_reserv` (`id_reservation`);

--
-- Index pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD PRIMARY KEY (`id_disponibilite`),
  ADD KEY `idx_presta_date` (`id_prestataire`,`date_debut`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `dispo_fk_reserv` (`id_reservation`);

--
-- Index pour la table `documents_presta`
--
ALTER TABLE `documents_presta`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `idx_presta_type` (`id_prestataire`,`type_document`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id_evaluation`),
  ADD UNIQUE KEY `uk_senior_reservation` (`id_senior`,`id_reservation`),
  ADD KEY `idx_presta` (`id_prestataire`),
  ADD KEY `idx_note` (`note`),
  ADD KEY `eval_fk_reserv` (`id_reservation`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `factures`
--
ALTER TABLE `factures`
  ADD PRIMARY KEY (`id_facture`),
  ADD UNIQUE KEY `uk_numero_facture` (`numero_facture`),
  ADD UNIQUE KEY `uk_presta_mois_annee` (`id_prestataire`,`mois`,`annee`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_paiement` (`id_paiement`);

--
-- Index pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  ADD PRIMARY KEY (`id_inscription`),
  ADD UNIQUE KEY `unique_inscription` (`id_senior`,`id_evenement`),
  ADD KEY `insc_ibfk_2` (`id_evenement`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `message_contact`
--
ALTER TABLE `message_contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_senior` (`id_senior`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id_paiement`),
  ADD UNIQUE KEY `uk_stripe_event` (`stripe_event_id`),
  ADD KEY `idx_type_objet` (`type_objet`,`objet_id`),
  ADD KEY `idx_payeur` (`id_payeur`),
  ADD KEY `idx_stripe_pi` (`stripe_payment_intent_id`),
  ADD KEY `idx_date` (`date_paiement`),
  ADD KEY `paie_fk_benef` (`id_beneficiaire`);

--
-- Index pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD PRIMARY KEY (`id_prestataire`);

--
-- Index pour la table `rdv_medical`
--
ALTER TABLE `rdv_medical`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_senior` (`id_senior`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `id_senior` (`id_senior`),
  ADD KEY `id_prestataire` (`id_prestataire`);

--
-- Index pour la table `senior`
--
ALTER TABLE `senior`
  ADD PRIMARY KEY (`id_senior`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `fk_services_presta` (`id_prestataire`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abonnement`
--
ALTER TABLE `abonnement`
  MODIFY `id_abonnement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories_prestations`
--
ALTER TABLE `categories_prestations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `conseil`
--
ALTER TABLE `conseil`
  MODIFY `id_conseil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `id_devis` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  MODIFY `id_disponibilite` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents_presta`
--
ALTER TABLE `documents_presta`
  MODIFY `id_document` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id_evaluation` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id_facture` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message_contact`
--
ALTER TABLE `message_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id_paiement` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rdv_medical`
--
ALTER TABLE `rdv_medical`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id_reservation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD CONSTRAINT `abonnement_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `devis_fk_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `devis_fk_reserv` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`) ON DELETE SET NULL,
  ADD CONSTRAINT `devis_fk_senior` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD CONSTRAINT `dispo_fk_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispo_fk_reserv` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`) ON DELETE SET NULL;

--
-- Contraintes pour la table `documents_presta`
--
ALTER TABLE `documents_presta`
  ADD CONSTRAINT `doc_fk_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `eval_fk_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `eval_fk_reserv` FOREIGN KEY (`id_reservation`) REFERENCES `reservation` (`id_reservation`) ON DELETE CASCADE,
  ADD CONSTRAINT `eval_fk_senior` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `fact_fk_paiement` FOREIGN KEY (`id_paiement`) REFERENCES `paiements` (`id_paiement`) ON DELETE SET NULL,
  ADD CONSTRAINT `fact_fk_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  ADD CONSTRAINT `insc_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `insc_ibfk_2` FOREIGN KEY (`id_evenement`) REFERENCES `evenements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notif_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paie_fk_benef` FOREIGN KEY (`id_beneficiaire`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `paie_fk_payeur` FOREIGN KEY (`id_payeur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `prestataire`
--
ALTER TABLE `prestataire`
  ADD CONSTRAINT `prestataire_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rdv_medical`
--
ALTER TABLE `rdv_medical`
  ADD CONSTRAINT `rdv_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `rdv_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `senior`
--
ALTER TABLE `senior`
  ADD CONSTRAINT `senior_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_services_presta` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

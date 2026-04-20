DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `commandes`;
DROP TABLE IF EXISTS `devis`;
DROP TABLE IF EXISTS `rdv_medicaux`;
DROP TABLE IF EXISTS `reservation`;
DROP TABLE IF EXISTS `prestataire`;
DROP TABLE IF EXISTS `senior`;
DROP TABLE IF EXISTS `conseils`;
DROP TABLE IF EXISTS `message_contact`;
DROP TABLE IF EXISTS `evenements`;
DROP TABLE IF EXISTS `categories_prestations`;
DROP TABLE IF EXISTS `article`;
DROP TABLE IF EXISTS `utilisateur`;

CREATE TABLE `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_utilisateur` enum('senior','prestataire','admin') NOT NULL,
  `est_actif` tinyint(1) DEFAULT 0,
  `token_confirmation` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE (`email`)
);

CREATE TABLE `senior` (
  `id_senior` int NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_senior`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE `prestataire` (
  `id_prestataire` int NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT 'domicile',
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','valide','suspendu') DEFAULT 'en_attente',
  `photo` varchar(255) DEFAULT NULL,
  `tarif_horaire` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_prestataire`),
  FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE `article` (
  `id_article` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_article`)
);

CREATE TABLE `categories_prestations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

CREATE TABLE `evenements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `date_debut` datetime DEFAULT NULL,
  `lieu` varchar(200) DEFAULT NULL,
  `nombre_places` int DEFAULT 20,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

CREATE TABLE `message_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

CREATE TABLE `reservation` (
  `id_reservation` int NOT NULL AUTO_INCREMENT,
  `id_senior` int NOT NULL,
  `id_prestataire` int NOT NULL,
  `date_reservation` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','confirme','termine','annule') DEFAULT 'en_attente',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reservation`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`),
  FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur`(`id_utilisateur`)
);

CREATE TABLE `conseils` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `categorie` varchar(100) DEFAULT 'general',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);

CREATE TABLE `devis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_prestataire` int NOT NULL,
  `id_senior` int NOT NULL,
  `montant` decimal(8,2) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT 'en_attente',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE `rdv_medicaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_senior` int NOT NULL,
  `id_medecin` int NOT NULL,
  `date_rdv` datetime NOT NULL,
  `statut` enum('en_attente','confirme','annule') DEFAULT 'en_attente',
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)  
    FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_medecin`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `id_expediteur` int NOT NULL,
  `id_destinataire` int NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_message`),
  FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE `commandes` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_senior` int NOT NULL,
  `id_article` int DEFAULT NULL,
  `nom_article` varchar(200) DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `statut` enum('en_attente','expediee','livree','annulee') DEFAULT 'en_attente',
  `stripe_payment_intent` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_commande`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_reservation` int NOT NULL,
  `id_senior` int NOT NULL,
  `id_prestataire` int NOT NULL,
  `note` tinyint NOT NULL CHECK (`note` BETWEEN 1 AND 5),
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reservation` (`id_reservation`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `abonnements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_senior` int NOT NULL,
  `type` enum('mensuel','annuel') NOT NULL,
  `prix` decimal(8,2) NOT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `statut` enum('actif','expire','annule') DEFAULT 'actif',
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reservation`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_senior` int NOT NULL,
  `titre` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reservation`),
  FOREIGN KEY (`id_senior`) REFERENCES `utilisateur`(`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `factures_archivees` (
  `id`             int NOT NULL AUTO_INCREMENT,
  `num_facture`    varchar(50) NOT NULL,
  `id_prestataire` int NOT NULL,
  `mois`           char(7) NOT NULL,
  `nb_prestations` int DEFAULT 0,
  `total_net`      decimal(10,2) DEFAULT 0,
  `pdf_path`       varchar(500) NOT NULL,
  `created_at`     timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `facture_unique` (`num_facture`)
);

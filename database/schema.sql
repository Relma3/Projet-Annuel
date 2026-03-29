SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;


-- Table : utilisateur  
DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_utilisateur` enum('senior','prestataire','admin') NOT NULL,
  `est_actif` tinyint(1) DEFAULT 0,
  `token_confirmation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : senior
DROP TABLE IF EXISTS `senior`;
CREATE TABLE `senior` (
  `id_senior` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_senior`),
  CONSTRAINT `senior_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : prestataire
DROP TABLE IF EXISTS `prestataire`;
CREATE TABLE `prestataire` (
  `id_prestataire` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT 'domicile',
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','valide','suspendu') DEFAULT 'en_attente',
  `photo` varchar(255) DEFAULT NULL,
  `tarif_horaire` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_prestataire`),
  CONSTRAINT `prestataire_ibfk_1` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : categories_prestations
DROP TABLE IF EXISTS `categories_prestations`;
CREATE TABLE `categories_prestations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : evenements
DROP TABLE IF EXISTS `evenements`;
CREATE TABLE `evenements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `date_debut` datetime DEFAULT NULL,
  `lieu` varchar(200) DEFAULT NULL,
  `nombre_places` int(11) DEFAULT 20,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : reservation
DROP TABLE IF EXISTS `reservation`;
CREATE TABLE `reservation` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `id_senior` int(11) NOT NULL,
  `id_prestataire` int(11) NOT NULL,
  `date_reservation` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en_attente','confirme','termine','annule') DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reservation`),
  KEY `id_senior` (`id_senior`),
  KEY `id_prestataire` (`id_prestataire`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`),
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_prestataire`) REFERENCES `utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : article
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id_article` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_article`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : commandes  
DROP TABLE IF EXISTS `commandes`;
CREATE TABLE `commandes` (
  `id_commande` int(11) NOT NULL AUTO_INCREMENT,
  `id_senior` int(11) NOT NULL,
  `id_article` int(11) DEFAULT NULL,
  `nom_article` varchar(200) DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `statut` enum('en_attente','expediee','livree','annulee') DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_commande`),
  KEY `id_senior` (`id_senior`),
  CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`id_senior`) REFERENCES `utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : messages  
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_expediteur` int(11) NOT NULL,
  `id_destinataire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_message`),
  KEY `id_expediteur` (`id_expediteur`),
  KEY `id_destinataire` (`id_destinataire`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`id_expediteur`) REFERENCES `utilisateur` (`id_utilisateur`),
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Table : message_contact  
DROP TABLE IF EXISTS `message_contact`;
CREATE TABLE `message_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sujet` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;


-- Données initiales
INSERT INTO `utilisateur` VALUES
(1,'admin@silverhappy.fr','$2y$10$7thWjBbuav4WkOIEf/BKeuogjkdWIlBlSsZ9vJRpM7bYK3lmkQzwCroot','admin',1,NULL,'2026-03-24 11:08:49'),
(2,'mmina.rathi@gmail.com','$2y$10$Uz4vq8uyGJkpjRbbD6TEAeP4dFzHbL1hERjarXkICe6BxbyyWiL1e','senior',0,'00a05c7b151e5a0d56dd210f45d76e3586432b0a6f9765afc8f6c9b2606a05a9','2026-03-24 13:49:27');

INSERT INTO `senior` VALUES
(2,'Rathi','Mmina',NULL,NULL,NULL);
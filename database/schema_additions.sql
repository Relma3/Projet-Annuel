CREATE TABLE IF NOT EXISTS abonnements (
  id int NOT NULL AUTO_INCREMENT,
  id_senior int NOT NULL,
  type enum('mensuel','annuel') NOT NULL DEFAULT 'annuel',
  prix decimal(8,2) NOT NULL,
  debut date NOT NULL,
  fin date NOT NULL,
  statut enum('actif','expire','resilie') NOT NULL DEFAULT 'actif',
  stripe_subscription_id varchar(255) DEFAULT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (id_senior) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
  id int NOT NULL AUTO_INCREMENT,
  id_senior int NOT NULL,
  titre varchar(255) NOT NULL,
  message text NOT NULL,
  lu tinyint(1) DEFAULT 0,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (id_senior) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS evaluations (
  id int NOT NULL AUTO_INCREMENT,
  id_reservation int NOT NULL,
  id_senior int NOT NULL,
  id_prestataire int NOT NULL,
  note tinyint NOT NULL,
  commentaire text DEFAULT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY eval_unique (id_reservation),
  FOREIGN KEY (id_reservation) REFERENCES reservation(id_reservation) ON DELETE CASCADE,
  FOREIGN KEY (id_senior) REFERENCES utilisateur(id_utilisateur),
  FOREIGN KEY (id_prestataire) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE IF NOT EXISTS factures_archivees (
  id int NOT NULL AUTO_INCREMENT,
  num_facture varchar(50) NOT NULL,
  id_prestataire int NOT NULL,
  mois char(7) NOT NULL,
  nb_prestations int DEFAULT 0,
  total_net decimal(10,2) DEFAULT 0,
  pdf_path varchar(500) NOT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY facture_unique (num_facture),
  FOREIGN KEY (id_prestataire) REFERENCES utilisateur(id_utilisateur)
);

CREATE INDEX idx_reservation_pres_statut ON reservation(id_prestataire, statut);
CREATE INDEX idx_reservation_senior ON reservation(id_senior);
CREATE INDEX idx_rdv_senior ON rdv_medicaux(id_senior);
CREATE INDEX idx_rdv_medecin ON rdv_medicaux(id_medecin);
CREATE INDEX idx_messages_dest ON messages(id_destinataire, lu);
CREATE INDEX idx_commandes_senior ON commandes(id_senior);
# SilverHappy – Projet Annuel

README de Mmina  

SEMAINE 1  

- Document fonctionnel complet
- Arborescence du site
- Schéma BDD (MCD)
- Documentation API
- Maquettes Admin (Figma)
- Trello DEV
- Gantt

SEMAINE 2

- Mise en place de l’API d’authentification (PHP + MySQL)
- Création des routes login, register senior, register prestataire
- Ajout de la route forgot-password 
- Hash des mots de passe + vérification
- Connexion frontend ↔ API avec fetch
- Tests des routes avec Postman
- Ajout d’un token simple à la connexion

SEMAINE 3

- Vrai JWT au login (remplacer le fake-token)
- Toutes les routes Admin (seniors, prestataires, catégories, événements)
- Dashboard Admin front (4 onglets)
- Compte admin créé en BDD
- Tests Postman des routes protégées
- GitHub + journal de bord

SEMAINE 4 

- Routes seniors (GET /seniors/me, PUT /seniors/me)
- Intégration Stripe (PaymentIntent)
- Page profil senior HTML/JS
- Tests Postman routes seniors + Stripe
- GitHub + journal de bord


















Installation pour moi (en local)

- Cloner le projet
- Mettre le dossier dans htdocs (MAMP/XAMPP)
- Créer la base de données
- Importer la table utilisateurs
- Configurer api/config/database.php
- Lancer : http://localhost/PA_2EME_ANNEE/frontend/connexion.html
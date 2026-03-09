# MEURTRE À HUIS CLOS

## Sommaire
- [Description](#description)
- [Technologies utilisées](#technologies-utilisées)
- [Environnement de travail](#environnement-de-travail)
- [Architecture du projet](#architecture-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Installation](#installation-du-projet)
- [Branches Git / Workflow](#branches-git--workflow)
- [Structure du projet](#structure-du-projet)
- [Auteur](#auteur)

---

## Description
**Meurtre à Huis Clos — Murder Party en ligne**
**Meutre à Huis Clos** est une plateforme de Murder Party numérique permettant à des groupes d'amis, de collègues ou des familles de vivre des expériences d'enquête immersives directement depuis leur smartphone.
L'idée est simple : proposer des scénarios de Murder Party clé en main, achetables en ligne (sur l'application web ou l'application mobile) et jouables via l'application mobile. Chaque scénario plonge les joueurs dans une enquête où ils incarnent des personnages, votent pour désigner le coupable et découvrent le dénouement.
Toutes les Murder Parties sont écrites par mes soins et sont des **productions originales***. 
Le projet se compose de deux parties complémentaires : 
- Un **site web Symfony** - vitrine commerciale, système d'achat et backoffice d'administration
- Une **application mobile Flutter** - coeur de l'expérience de jeu ([voir le repo Flutter](https://github.com/alicegby/m8c-app.git))

---

## Technologies utilisées

### Backend
- **Langage principal** : PHP 8.4
- **Framework web** : Symfony 8
- **Gestion base de données** : Doctrine ORM 3.6
- **Moteur de templates** : Twig 3.23

### Base de données
- **Base de données relationnelle** : PostgreSQL
- **Base de données non relationnelle** : PostgreSQL JSONB
- **Hébergement BDD + Auth + Storage** : Supabase

### Services externes
- **Paiement en ligne (checkout, webhooks)** : Stripe
- **Authentification des utilisateurs app mobile** : Supabe Auth
- **Newsletters** : Brevo

### Frontend
- HTML
- CSS
- JavaScript

### Outils du backoffice administrateur
- **Graphiques des statistiques** : Chart.js
- **Sélecteurs de date** : Flatpickr

--- 

## Environnement de travail
- **Système d'exploitation** : macOS Sequoia 15.7.3
- **Serveur local** : Symfony CLI 5.16.1
- **IDE** : VS Code
- **Navigateur de test** : Chrome
- **Base de données locale** : Supabase (cloud)
- **Tests paiements** : Stripe CLI

--- 

## Architecture du projet

Application Web Symfony -> Vitrine publique (catalogue, téléchargement du jeu, panier, checkout, pages d'informations)
                                            -> Authentification Symfony (admin / user)
                                            -> Backoffice admin (gestion des Murder Parties, des codes promos, des avatars, des packs, des avis, des achats, vue clients)
                                            -> Webhooks Stripe (confirmation achats)
                                            -> API REST interne (game sessions pour l'app Flutter)
                                            --> Supabase (PostgreSQL + Auth + Storage)
                                            ---> Application Mobile Flutter (repo séparé)

### Flux d'achat
Utilisateur -> Catalogue Murder Parties et/ou Packs -> Panier -> Stripe Checkout -> Webhood -> Purchase créé -> UserMurderParty débloquée
Les joueurs ont la possibilité d'acheter également directement sur l'application, géré directement par Apple et Android. 

--- 

## Fonctionnalités

### Vitrine publique
- Catalogue des Murder Parties avec synopsis, durée, nombre de joueurs, note moyenne, prix
- Avis sur le jeu au global
- Page avec formulaire de contact
- Panier multi-articles (scénarios unitaires & packs - mais on ne peut pas commander deux fois le même article, la quantité est toujours de 1)
- Codes promos (pourcentage ou montant fixe, limites d'utilisation, dates de validité)
- Paiement sécurisé via Stripe Checkout
- Emails transactionnels (confirmation inscription, vérification email, réinitilisation du mot de passe)

### Authentification
- Inscription avec vérification d'email
- Connexion / Déconnexion
- Réinitilisation de mot de passe
- Synchronisation avec Supabase Auth (pour l'app mobile)

### Espace Utilisateur 
- Historique des achats
- Historique des Murder Parties jouées
- Gestion du profil (modifications des informations personnelles)

### Backoffice Administrateur
Toutes les fonctionnalités sont filtrables : 
- Gestion des Murder Parties (CRUD, publication/dépublication)
- Gestion des personnages et indices par Murder Party (CRUD)
- Gestion des packs (CRUD)
- Gestion des codes promos (CRUD)
- Gestion des avatars (CRUD)
- Vue de la liste des clients
- Tableau de bord statistiques : 
    Filtrables par Murder Parties et par période. 
    - Chiffre d'affaires et ventes par Murder Party
    - Pourcentage de coupable trouvé par Murder Party
    - Achats avec code promos vs prix plein
    - Panier moyen
    - Répartition des méthodes de paiement
    - Taux de retours joueurs
    - Murder Party notées vs vendues
    - Inscriptions par mois (géré en NoSQL)
    - Achats Web vs Application 

### Légal
- Pages légales : Mentions Légales, Conditions Générales de Vente, Politique de Confidentialité 
- Page d'avertissement du contenu sensible du jeu 

--- 

## Installation du projet

### Prérequis
- PHP 8.4+
- Composer
- Symfony CLI
- Un projet Supabase (PostgreSQL)
- Un compte Stripe
- Stripe CLI

### Étapes
**1. Cloner le repo**
    ''bash - git clone https://github.com/alicegby/M8C.git 

**2. Installer les dépendances**
    ''bash - composer install

**3. Configurer les variables d'environnement**
    ''bash - cp .env .env.local

    Remplir .env.local avec : 
    - DATABASE_URL
    - SUPABASE_URL
    - SUPABASE_ANON_KEY
    - SUPABASE_SERVICE_ROLE_KEY
    - MAILER_DSN
    - STRIPE_PUBLIC_KEY
    - STRIPE_SECRET_KEY
    - STRIPE_WEBHOOK_SECRET
    - Admin & User Fixtures (emails, mots de passe, noms, prénoms, dates de naissances)

**4. Créer la base de données et migrer**
    ''bash - php bin/console doctrine:migrations:migrate

**5. Lancer le serveur local**
    ''bash - symfony serve

**6. Lancer le tunnel Stripe (webhooks en local)**
    ''bash - stripe listen --forward-to localhost:8000/webhook/stripe

--- 
## Branches Git

### Branche main
Branche de la version stable et finalisée du code

### Branche develop
Branche de développement principale, une fois validé, migration vers la branche main

---

## Structure du projet
M8CWebsite
    config/ : configuration Symfony
    migrations/ : migrations Doctrine
    public/
        css/ : Styles custom
        fonts/ : Polices du projet
        icones/ : Iconographie du projet
        images/ : images du projet
        js/ : Dynamisme des pages
        uploads/ : géré avec les paramètres dans config/services.yaml pour les avatars majoritairement
    src/ 
        Command/ : Commandes Symfony
        Controller/ : Controllers public, admin, API
            Admin/ : Controllers backoffice
        DataFixtures/ : Données BDD
        Entity/ : Entités Doctrine
        Form/ : Formulaires Symfony
        Repository/ : Repositories Doctrine
        Security/ : Gestion des routes après connexion selon les rôles
        Service/ : Services métier (Cart, Stat, JoinCode)
    templates/ 
        account/ : Template de modification des informations des utilisateurs
        admin/ : Templates backoffice
        emails/ : Templates emails
        security/ : Template de connexion
        *.html.twig : Templates publics
    .env : Variables d'environnement non commitées
    .env.local
    composer.json
    README.md
    
--- 

## Auteur
Alice Gruby
Étudiante en Formation - Graduate Développeur Web & Web Mobile
**Ce projet est une production originale, et un projet personnelle. Les scénarios des Murder Parties sont protégés par des droits d'auteur.**
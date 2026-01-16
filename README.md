# CampusFlow Version 2

Application web de gestion académique complète destinée aux établissements d'enseignement supérieur.

## 🎯 Fonctionnalités

- **Gestion des étudiants** : CRUD complet avec recherche et filtres
- **Gestion des classes** : Création, modification, affectation d'étudiants
- **Gestion des enseignants** : Profils, matières enseignées, classes assignées
- **Gestion des matières** : Catalogue des matières avec coefficients
- **Emploi du temps** : Création de créneaux avec détection de conflits
- **Authentification multi-rôles** : Admin, Enseignant, Étudiant
- **Interface moderne** : Design responsive avec Tailwind CSS 3

## 🛠️ Technologies

- **Backend** : PHP Symfony 7.0
- **Frontend** : Tailwind CSS 3
- **Base de données** : MySQL
- **ORM** : Doctrine

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL 5.7+ ou MariaDB 10.3+
- XAMPP ou serveur web équivalent

## 🚀 Installation

### 1. Cloner le projet

```bash
cd C:\xampp\htdocs\CampusFlow_version
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer la base de données

Créez un fichier `.env.local` à la racine du projet :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/campusflow?serverVersion=8.0.32&charset=utf8mb4"
```

Créez la base de données :

```bash
php bin/console doctrine:database:create
```

### 4. Créer les migrations et la structure de la base de données

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

### 6. Créer le dossier pour les uploads

Le dossier `public/uploads/profiles` doit exister (déjà créé).

### 7. Lancer le serveur de développement

```bash
symfony server:start
```

Ou utilisez le serveur XAMPP en pointant vers le dossier `public/`.

## 👤 Comptes de test

Après avoir chargé les fixtures, vous pouvez vous connecter avec :

### Administrateur
- **Email** : `admin.campusflow@campusflow.com`
- **Mot de passe** : `azsq`

### Enseignant
- **Email** : `yassine.elamrani@campusflow.com`
- **Mot de passe** : `azsq`

### Étudiant
- **Email** : `imad.amrani@campusflow.com`
- **Mot de passe** : `azsq`

## 📁 Structure du projet

```
CampusFlow_version2/
├── src/
│   ├── Controller/
│   │   ├── Admin/          # Contrôleurs admin
│   │   ├── Teacher/        # Contrôleurs enseignant
│   │   ├── Student/        # Contrôleurs étudiant
│   │   └── SecurityController.php
│   ├── Entity/             # Entités Doctrine
│   ├── Form/               # Formulaires Symfony
│   ├── Repository/         # Repositories Doctrine
│   └── DataFixtures/       # Fixtures de test
├── templates/
│   ├── admin/              # Templates admin
│   ├── teacher/            # Templates enseignant
│   ├── student/            # Templates étudiant
│   └── security/           # Templates sécurité
├── public/                 # Point d'entrée web
└── config/                 # Configuration Symfony
```

## 🔐 Rôles et permissions

### ROLE_ADMIN
- Accès complet à toutes les fonctionnalités
- Gestion des étudiants, classes, enseignants, matières
- Création et gestion de l'emploi du temps

### ROLE_TEACHER
- Consultation de son profil
- Consultation de son emploi du temps
- Consultation des classes assignées

### ROLE_STUDENT
- Consultation de son profil
- Consultation de l'emploi du temps de sa classe
- Consultation des informations de classe

## 📝 Notes importantes

1. **Upload de fichiers** : Les photos de profil sont stockées dans `public/uploads/profiles/`
2. **Conflits d'emploi du temps** : Le système détecte automatiquement les conflits (enseignant, classe, salle)
3. **Durée des cours** : Entre 1h et 4h (validation automatique)
4. **Plage horaire** : 8h00 - 18h00

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres dans `.env.local`

### Erreur 404
- Videz le cache : `php bin/console cache:clear`

### Problèmes avec Tailwind CSS
- Recompilez les assets : `php bin/console tailwind:build`

## 📄 Licence

Projet académique - Tous droits réservés

## 👨‍💻 Développement

Pour contribuer au projet :

1. Créez une branche pour votre fonctionnalité
2. Commitez vos changements
3. Poussez vers la branche
4. Créez une Pull Request

---

**CampusFlow** - Système de gestion académique moderne et intuitif 🎓


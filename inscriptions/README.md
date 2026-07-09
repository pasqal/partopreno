# 📋 Système d'Inscription aux Lignes / Événements

Un site simple et léger pour gérer des inscriptions à des listes (lignes, événements, etc.) avec une **partie utilisateur** et une **partie administrateur**. Conçu pour être hébergé sur un **hébergement mutualisé** (PHP 7.4+ requis).

---

## ✅ Fonctionnalités

### 👥 **Partie Utilisateur**
- Accueil avec la liste des événements/lignes disponibles.
- Accès sécurisé par **mot de passe** (si configuré par l'admin).
- Tableau interactif pour :
  - **S'inscrire** en cliquant sur une cellule.
  - **Se désinscrire** en recliquant.
  - Voir les inscriptions des autres utilisateurs en temps réel.
- Design **responsive** (mobile/desktop/tablette).

### 🔐 **Partie Administrateur**
- Interface sécurisée avec **mot de passe admin** (configurable).
- **Créer/Modifier/Supprimer** des listes (événements/lignes).
- **Configurer un mot de passe** pour chaque liste (optionnel).
- **Importer** des listes depuis un fichier **CSV ou TXT**.
- **Exporter** les listes et inscriptions au format **CSV**.

### 💾 **Stockage**
- **Fichiers plats** (JSON/CSV) : pas besoin de base de données MySQL.
- Structure légère et facile à sauvegarder.

---

## 🚀 Installation

### 1️⃣ Prérequis
- Hébergement mutualisé avec **PHP 7.4 ou supérieur**.
- Accès en écriture aux dossiers `includes/data/` et `includes/data/registrations/`.

### 2️⃣ Téléchargement
1. Téléchargez ou clonez ce dépôt.
2. Copiez le dossier `inscriptions/` à la racine de votre hébergement (ex: `/public_html/inscriptions/`).
3. Assurez-vous que les dossiers suivants sont **écrivables** (CHMOD 755 ou 777) :
   - `includes/data/`
   - `includes/data/registrations/`

### 3️⃣ Configuration

#### 🔧 Configurer l'admin
1. Ouvrez le fichier `includes/config.php`.
2. Modifiez les lignes suivantes :
   ```php
   define('ADMIN_USERNAME', 'admin');       // Nom d'utilisateur admin
   define('ADMIN_PASSWORD_HASH', '...');     // Hash du mot de passe admin
   ```
   - Pour générer un hash, utilisez ce code PHP :
     ```php
     echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);
     ```
     Copiez le résultat dans `ADMIN_PASSWORD_HASH`.

#### 📝 Configurer les listes par défaut (optionnel)
- Le fichier `includes/data/lists.json` contient un exemple de liste.
- Vous pouvez le modifier manuellement ou utiliser l'interface admin pour créer vos listes.

---

## 📂 Structure des Fichiers

```
inscriptions/
│── admin/
│   ├── index.php        # Tableau de bord admin
│   ├── login.php        # Connexion admin
│   ├── manage.php       # Gestion des listes
│   ├── import.php       # Import CSV/TXT
│   └── export.php       # Export CSV
│
│── user/
│   ├── index.php        # Liste des événements
│   ├── list.php         # Tableau d'inscription
│   └── auth.php         # Vérification mot de passe
│
│── includes/
│   ├── config.php       # Configuration (mots de passe, chemins)
│   ├── functions.php    # Fonctions utilitaires
│   └── data/
│       ├── lists.json   # Liste des événements
│       └── registrations/ # Dossier des inscriptions (1 fichier JSON par liste)
│
│── assets/
│   ├── css/
│   │   └── style.css    # Styles (responsive)
│   └── js/
│       └── script.js    # Logique frontend
│
│── index.php            # Page d'accueil (redirection)
└── README.md            # Ce fichier
```

---

## 🎯 Utilisation

### 👥 Pour les **Utilisateurs**
1. Accédez à l'URL : `votre-site.com/inscriptions/` ou `votre-site.com/inscriptions/user/`.
2. Sélectionnez une liste dans la page d'accueil.
3. Si la liste est protégée par un mot de passe :
   - Saisissez le mot de passe fourni par l'admin.
4. Cliquez sur une cellule du tableau pour **vous inscrire**.
5. Recliquez sur la même cellule pour **vous désinscrire**.

### 🔐 Pour les **Administrateurs**
1. Accédez à : `votre-site.com/inscriptions/admin/`.
2. Connectez-vous avec le **nom d'utilisateur** et **mot de passe** configurés dans `config.php`.
3. **Gérer les listes** :
   - **Ajouter** : Cliquez sur "Nouvelle liste", remplissez le formulaire.
   - **Modifier** : Cliquez sur le bouton « Modifier » d'une liste.
   - **Supprimer** : Cliquez sur le bouton « Supprimer » (attention, irréversible).
4. **Configurer un mot de passe** pour une liste :
   - Dans le formulaire de modification, ajoutez un mot de passe dans le champ dédié.
5. **Importer une liste** :
   - Allez dans l'onglet « Importer ».
   - Sélectionnez un fichier **CSV ou TXT** (format : `Nom de la liste,Nom1,Nom2,Nom3,...`).
6. **Exporter une liste** :
   - Allez dans l'onglet « Exporter ».
   - Sélectionnez une liste et téléchargez le fichier CSV.

---

## 📄 Formats des Fichiers

### 📥 Import CSV/TXT
Le fichier doit avoir le format suivant (séparateur : virgule `,`) :
```csv
Nom de la liste,Nom1,Nom2,Nom3
Exemple,Ligne 1,Ligne 2,Ligne 3
```
- La **première ligne** est ignorée (en-tête).
- La **deuxième ligne** contient le nom de la liste et les noms des colonnes.

### 📤 Export CSV
Le fichier exporté aura le format :
```csv
"Nom de la liste","Nom1","Nom2","Nom3"
"Utilisateur1","","X",""
"Utilisateur2","X","","X"
```
- `X` = inscrit, vide = non inscrit.

---

## 🔒 Sécurité

### ⚠️ Recommandations
1. **Protégez le dossier `admin/`** :
   - Ajoutez une règle `.htaccess` pour restreindre l'accès par IP (si possible).
   - Exemple :
     ```apache
     Order Deny,Allow
     Deny from all
     Allow from 123.456.789.0
     ```
2. **Changez le mot de passe admin par défaut** dans `config.php`.
3. **Ne partagez pas** le mot de passe admin.
4. **Sauvegardez régulièrement** les fichiers dans `includes/data/`.

### 🛡️ Mesures implémentées
- Mots de passe admin **hashés** (SHA-256 + sel).
- Vérification des **CSRF tokens** pour les formulaires sensibles.
- **Fichiers JSON/CSV** non accessibles directement via URL (`.htaccess`).

---

## 🐛 Dépannage

| Problème | Solution |
|----------|----------|
| **Erreur 500** lors de l'accès à une page | Vérifiez que PHP 7.4+ est installé. |
| **Fichiers non écrivables** | Donnez les permissions `755` ou `777` aux dossiers `includes/data/` et `includes/data/registrations/`. |
| **Les inscriptions ne s'enregistrent pas** | Vérifiez que le fichier `includes/data/registrations/` existe et est accessible en écriture. |
| **Mot de passe admin non reconnu** | Régénérez le hash avec `password_hash()` et mettez à jour `config.php`. |
| **Import CSV échoue** | Vérifiez le format du fichier (voir section [Formats des Fichiers](#-formats-des-fichiers)). |

---

## 📌 Exemple de Configuration

### Fichier `includes/config.php`
```php
<?php
// Configuration de l'admin
define('ADMIN_USERNAME', 'mon_admin');
define('ADMIN_PASSWORD_HASH', '5f4dcc3b5aa765d61d8327deb882cf99'); // Hash de "password"

// Chemins
define('DATA_DIR', __DIR__ . '/data/');
define('REGISTRATIONS_DIR', DATA_DIR . 'registrations/');

// Options
define('DEBUG_MODE', false); // Affiche les erreurs PHP si true
```

### Fichier `includes/data/lists.json` (exemple)
```json
[
  {
    "id": 1,
    "name": "Lignes de Bus",
    "password": "bus123",
    "columns": ["Ligne 1", "Ligne 2", "Ligne 3"],
    "created_at": "2024-01-01 10:00:00"
  },
  {
    "id": 2,
    "name": "Événements Sportifs",
    "password": null,
    "columns": ["Football", "Tennis", "Natation"],
    "created_at": "2024-01-02 14:30:00"
  }
]
```

---

## 🔄 Mises à Jour

Pour mettre à jour le site :
1. Sauvegardez vos fichiers dans `includes/data/`.
2. Remplacez tous les fichiers **sauf** `includes/data/` et `includes/config.php`.
3. Vérifiez que les nouvelles fonctionnalités sont compatibles avec votre configuration.

---

## 📜 Licence

Ce projet est sous licence **MIT**. Vous êtes libre de l'utiliser, le modifier et le redistribuer.

---

## 🙏 Remerciements

- Inspiré par [l'exemple sur websim.com](https://websim.com/@icynight81829253/inscription-aux-lignes).
- Développé avec PHP, HTML5, CSS3 et JavaScript vanilla.

---

## 📧 Support

Pour toute question ou problème, ouvrez une **issue** sur le dépôt GitHub ou contactez l'administrateur du site.

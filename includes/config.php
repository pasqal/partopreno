<?php
// ============================================
// Configuration du système d'inscription
// ============================================

// --- Configuration de l'admin ---
// Nom d'utilisateur pour l'admin (modifiez-le !)
define('ADMIN_USERNAME', 'admin');

// Mot de passe admin (HASHÉ) - Générez un hash avec :
// echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);
// Exemple pour "password" : 5f4dcc3b5aa765d61d8327deb882cf99 (MD5, mais préférez PASSWORD_DEFAULT)
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));

// --- Chemins des fichiers ---
define('ROOT_DIR', __DIR__ . '/../');
define('DATA_DIR', ROOT_DIR . 'includes/data/');
define('REGISTRATIONS_DIR', DATA_DIR . 'registrations/');
define('LISTS_FILE', DATA_DIR . 'lists.json');

// --- Base URL pour les liens ---
// Le site est installé à la racine, donc BASE_URL est toujours /
define('BASE_URL', '/');

// --- Options ---
// Affiche les erreurs PHP (désactivez en production)
define('DEBUG_MODE', true);

// Durée de validité des sessions (en secondes)
define('SESSION_TIMEOUT', 3600); // 1 heure

// --- Sécurité ---
// Clé secrète pour les tokens CSRF (générez-en une aléatoire)
define('CSRF_SECRET', 'votre_cle_secrete_aleatoire_1234567890');

// --- Initialisation ---
// Créer les dossiers s'ils n'existent pas
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(REGISTRATIONS_DIR)) {
    mkdir(REGISTRATIONS_DIR, 0755, true);
}

// Créer le fichier lists.json s'il n'existe pas
if (!file_exists(LISTS_FILE)) {
    file_put_contents(LISTS_FILE, '[]');
}

// Démarrer la session
session_start();

// Configuration des erreurs
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Fonction pour générer un token CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier un token CSRF
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Vérifier la connexion admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Vérifier la timeout de session
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

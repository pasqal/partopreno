<?php
// ============================================
// Configuration du système d'inscription
// ============================================

// --- Configuration de l'admin ---
// Nom d'utilisateur pour l'admin (modifiez-le !)
define('ADMIN_USERNAME', 'admin');

// Mot de passe admin (HASHÉ) - Générez un hash avec :
// echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));

// --- Chemins des fichiers ---
define('ROOT_DIR', __DIR__ . '/../');
define('DATA_DIR', ROOT_DIR . 'includes/data/');
define('REGISTRATIONS_DIR', DATA_DIR . 'registrations/');
define('LISTS_FILE', DATA_DIR . 'lists.json');

// --- Base URL pour les liens ---
// Détecter automatiquement la base URL en fonction du script actuel
$script_name = $_SERVER['SCRIPT_NAME'];
$base_url = '/';

// Si le script est dans un sous-dossier (user/ ou admin/), on remonte à la racine
if (strpos($script_name, '/user/') !== false || strpos($script_name, '/admin/') !== false) {
    $base_url = '/';
}

define('BASE_URL', $base_url);

// --- Options ---
define('DEBUG_MODE', true);
define('SESSION_TIMEOUT', 3600); // 1 heure

// --- Sécurité ---
define('CSRF_SECRET', 'votre_cle_secrete_aleatoire_1234567890');

// --- Initialisation ---
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(REGISTRATIONS_DIR)) {
    mkdir(REGISTRATIONS_DIR, 0755, true);
}
if (!file_exists(LISTS_FILE)) {
    file_put_contents(LISTS_FILE, '[]');
}

session_start();

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

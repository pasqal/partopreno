<?php
// ============================================
// Connexion Administrateur
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'admin est déjà connecté
if (isAdminLoggedIn()) {
    redirect(BASE_URL . 'admin/index.php');
}

// Gérer la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Vérifier les identifiants
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['last_activity'] = time();
        redirect(BASE_URL . 'admin/index.php');
    } else {
        $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="login-box">
        <h1>Connexion Admin</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>
        
        <p class="mt-1 text-center">
            <a href="<?php echo BASE_URL; ?>user/">Accès utilisateur</a>
        </p>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

<?php
// ============================================
// Vérification du mot de passe pour une liste
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'ID de la liste est fourni
if (!isset($_GET['id'])) {
    redirect(url('user/index.php'));
}

$listId = (int)$_GET['id'];
$list = getListById($listId);

if (!$list) {
    redirect(url('user/index.php'));
}

// Vérifier si la liste est active
if (!($list['is_active'] ?? true)) {
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container">';
    echo '<div class="alert alert-error">';
    echo '<h2>Liste fermée</h2>';
    echo '<p>Cette liste ("' . htmlspecialchars($list['name']) . '") est actuellement fermée et n\'est pas accessible.</p>';
    echo '<p><a href="' . url('user/index.php') . '" class="btn btn-primary">Retour à la liste des événements</a></p>';
    echo '</div>';
    include __DIR__ . '/../includes/footer.php';
    exit();
}

// Vérifier si la liste nécessite un mot de passe
if (empty($list['password'])) {
    // Pas de mot de passe requis, rediriger vers la liste
    // On ne stocke pas list_access car on veut forcer la saisie du nom
    redirect(url('user/list.php?id=' . $listId));
}

// FORCER la saisie du nom pour chaque liste - pas de session globale
$sessionKey = 'list_user_name_' . $listId;
$userName = isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : '';

$nameWarning = '';

// Gérer la soumission du nom d'utilisateur
if (empty($userName) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_user_name'])) {
    $newUserName = sanitizeInput($_POST['set_user_name']);
    
    if (!empty($newUserName)) {
        // Vérifier si le nom est déjà utilisé dans cette liste (juste pour information)
        $registrations = getAllRegistrations($listId);
        $nameExists = isset($registrations[$newUserName]);
        
        if ($nameExists) {
            $nameWarning = '⚠️ Ce nom est déjà utilisé par un autre utilisateur dans cette liste. Vous pouvez tout de même l\'utiliser.';
        }
        
        // Toujours accepter le nom et le stocker pour cette liste
        $_SESSION[$sessionKey] = $newUserName;
        $userName = $newUserName;
    }
}

// Vérifier si le mot de passe a été soumis
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (checkListPassword($listId, $password)) {
        // Stocker l'accès à la liste (mais pas le nom, qui sera demandé à chaque fois)
        $_SESSION['list_access_' . $listId] = true;
        redirect(url('user/list.php?id=' . $listId));
    } else {
        $error = 'Mot de passe incorrect.';
    }
}

// Afficher le formulaire
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Accès à : <?php echo htmlspecialchars($list['name']); ?></h1>
    <p>Cette liste est protégée par un mot de passe. Veuillez le saisir pour continuer.</p>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($userName)): ?>
        <!-- Formulaire pour saisir le nom avant le mot de passe -->
        <div class="name-input-container">
            <form method="post" action="" class="name-form">
                <div class="form-group name-group">
                    <label for="set_user_name">Votre nom pour cette liste *</label>
                    <div class="input-with-warning">
                        <input type="text" id="set_user_name" name="set_user_name" placeholder="Entrez votre nom" required autofocus>
                        <button type="submit" class="btn btn-primary">Valider</button>
                    </div>
                    <?php if (!empty($nameWarning)): ?>
                        <div class="alert alert-warning name-warning">
                            <?php echo $nameWarning; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Formulaire de mot de passe -->
        <form method="post" action="">
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Accéder à la liste</button>
            <a href="<?php echo url('user/index.php'); ?>" class="btn btn-secondary">Annuler</a>
        </form>
    <?php endif; ?>
</div>

<style>
    /* Style pour le formulaire de nom */
    .name-input-container {
        margin: 20px 0;
        padding: 20px;
        background-color: #fff3cd;
        border-radius: 8px;
        border: 2px solid #ffc107;
    }
    .name-form {
        max-width: 500px;
    }
    .name-group {
        margin-bottom: 0;
    }
    .name-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    .input-with-warning {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .input-with-warning input {
        flex: 1;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    .input-with-warning input:focus {
        outline: none;
        border-color: #4a6fa5;
    }
    .name-warning {
        margin-top: 10px;
        padding: 8px 12px;
        font-size: 14px;
    }
</style>

<?php
include __DIR__ . '/../includes/footer.php';

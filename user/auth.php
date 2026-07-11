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

// Vérifier si la liste nécessite un mot de passe
if (empty($list['password'])) {
    // Pas de mot de passe requis, rediriger vers la liste
    $_SESSION['list_access_' . $listId] = true;
    redirect(url('user/list.php?id=' . $listId));
}

// Gérer la soumission du nom d'utilisateur (si pas encore de nom)
$userName = getCurrentUserName();
$nameWarning = '';

if (empty($userName) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_user_name'])) {
    $newUserName = sanitizeInput($_POST['set_user_name']);
    
    if (!empty($newUserName)) {
        // Vérifier si le nom est déjà utilisé (juste pour information)
        $allLists = loadLists();
        $nameExists = false;
        
        foreach ($allLists as $listItem) {
            $listIdCheck = $listItem['id'];
            $registrations = getAllRegistrations($listIdCheck);
            if (isset($registrations[$newUserName])) {
                $nameExists = true;
                break;
            }
        }
        
        if ($nameExists) {
            $nameWarning = '⚠️ Ce nom est déjà utilisé par un autre utilisateur. Vous pouvez tout de même l\'utiliser.';
        }
        
        // Toujours accepter le nom
        setCurrentUserName($newUserName);
        $userName = $newUserName;
    }
}

// Vérifier si le mot de passe a été soumis
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (checkListPassword($listId, $password)) {
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
                    <label for="set_user_name">Votre nom *</label>
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
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #ddd;
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

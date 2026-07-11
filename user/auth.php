<?php
// ============================================
// Vérification du mot de passe pour une liste
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'ID de la liste est fourni
if (!isset($_GET['id'])) {
    redirect(BASE_URL . 'user/index.php');
}

$listId = (int)$_GET['id'];
$list = getListById($listId);

if (!$list) {
    redirect(BASE_URL . 'user/index.php');
}

// Vérifier si la liste nécessite un mot de passe
if (empty($list['password'])) {
    // Pas de mot de passe requis, rediriger vers la liste
    $_SESSION['list_access_' . $listId] = true;
    redirect(BASE_URL . 'user/list.php?id=' . $listId);
}

// Vérifier si le mot de passe a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (checkListPassword($listId, $password)) {
        $_SESSION['list_access_' . $listId] = true;
        redirect(BASE_URL . 'user/list.php?id=' . $listId);
    } else {
        $error = 'Mot de passe incorrect.';
    }
}

// Afficher le formulaire de mot de passe
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Accès à : <?php echo htmlspecialchars($list['name']); ?></h1>
    <p>Cette liste est protégée par un mot de passe. Veuillez le saisir pour continuer.</p>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary">Accéder à la liste</button>
        <a href="<?php echo BASE_URL; ?>user/index.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

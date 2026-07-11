<?php
// ============================================
// Partie Utilisateur - Liste des événements disponibles
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'utilisateur a un nom
$userName = getCurrentUserName();
if (empty($userName)) {
    // Demander un nom d'utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
        $userName = sanitizeInput($_POST['user_name']);
        if (!empty($userName)) {
            setCurrentUserName($userName);
            redirect(url('user/index.php'));
        }
    }
    
    // Afficher le formulaire de nom d'utilisateur
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container">';
    echo '<h1>Bienvenue</h1>';
    echo '<p>Veuillez entrer votre nom pour commencer :</p>';
    echo '<form method="post" action="">';
    echo '<input type="text" name="user_name" placeholder="Votre nom" required autofocus>';
    echo '<button type="submit" class="btn btn-primary">Continuer</button>';
    echo '</form>';
    echo '</div>';
    include __DIR__ . '/../includes/footer.php';
    exit();
}

// Gérer la déconnexion
if (isset($_GET['logout'])) {
    unset($_SESSION['user_name']);
    setcookie('user_name', '', time() - 3600, '/');
    redirect(url('user/index.php'));
}

// Charger les listes
$lists = loadLists();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Bonjour, <?php echo htmlspecialchars($userName); ?> !</h1>
    <p>Voici les listes disponibles pour vous inscrire :</p>
    
    <?php if (empty($lists)): ?>
        <div class="alert alert-info">
            Aucune liste n'est disponible pour le moment. Veuillez revenir plus tard.
        </div>
    <?php else: ?>
        <div class="list-grid">
            <?php foreach ($lists as $list): ?>
                <div class="list-card">
                    <h3><?php echo htmlspecialchars($list['name']); ?></h3>
                    
                    <?php if (!empty($list['description'])): ?>
                        <div class="list-description">
                            <?php echo nl2br(htmlspecialchars($list['description'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="list-meta">
                        <p>
                            <strong>ID:</strong> <?php echo $list['id']; ?><br>
                            <strong>Colonnes :</strong> <?php echo count($list['columns']); ?>
                        </p>
                        <p>
                            <strong>Mot de passe :</strong> 
                            <?php echo !empty($list['password']) ? '<span class="badge badge-warning">Oui</span>' : '<span class="badge badge-success">Non</span>'; ?>
                        </p>
                        <p>
                            <strong>Créé le :</strong> <?php echo date('d/m/Y H:i', strtotime($list['created_at'])); ?>
                        </p>
                    </div>
                    
                    <a href="<?php echo url('user/list.php?id=' . $list['id']); ?>" class="btn btn-primary">
                        Voir la liste
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="mt-2">
        <a href="<?php echo url('user/?logout=1'); ?>" class="btn btn-secondary">Changer de nom</a>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

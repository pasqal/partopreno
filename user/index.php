<?php
// ============================================
// Partie Utilisateur - Liste des événements disponibles
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'utilisateur a un nom
$userName = getCurrentUserName();

// Gérer la déconnexion
if (isset($_GET['logout'])) {
    unset($_SESSION['user_name']);
    setcookie('user_name', '', time() - 3600, '/');
    redirect(url('user/index.php'));
}

// Gérer la soumission du nom d'utilisateur
if (empty($userName)) {
    $warning = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
        $newUserName = sanitizeInput($_POST['user_name']);
        
        // Vérifier si le nom est déjà utilisé (juste pour information)
        if (!empty($newUserName)) {
            $allLists = loadLists();
            $nameExists = false;
            
            foreach ($allLists as $list) {
                $listId = $list['id'];
                $registrations = getAllRegistrations($listId);
                if (isset($registrations[$newUserName])) {
                    $nameExists = true;
                    break;
                }
            }
            
            if ($nameExists) {
                $warning = '⚠️ Ce nom est déjà utilisé par un autre utilisateur. Vous pouvez tout de même l\'utiliser.';
            }
            
            // Toujours accepter le nom
            setCurrentUserName($newUserName);
            redirect(url('user/index.php'));
        }
    }
    
    // Afficher le formulaire de nom d'utilisateur
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container">';
    echo '<h1>Bienvenue</h1>';
    echo '<p>Veuillez entrer votre nom pour commencer :</p>';
    
    if (!empty($warning)):
        echo '<div class="alert alert-warning">' . $warning . '</div>';
    endif;
    
    echo '<form method="post" action="">';
    echo '<input type="text" name="user_name" placeholder="Votre nom" required autofocus>';
    echo '<button type="submit" class="btn btn-primary">Continuer</button>';
    echo '</form>';
    echo '</div>';
    include __DIR__ . '/../includes/footer.php';
    exit();
}

// Charger les listes
$lists = loadLists();

// Si une seule liste existe, rediriger directement vers elle
if (count($lists) === 1) {
    redirect(url('user/list.php?id=' . $lists[0]['id']));
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1>Bonjour, <?php echo htmlspecialchars($userName); ?> !</h1>
        <div class="user-info">
            <span>Connecté en tant que : <strong><?php echo htmlspecialchars($userName); ?></strong></span>
        </div>
    </div>
    
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
                            <strong>Colonnes :</strong> <?php 
                                $colCount = 0;
                                if (!empty($list['columns'])) {
                                    if (isset($list['columns'][0]['name'])) {
                                        $colCount = count($list['columns']);
                                    } else {
                                        $colCount = count($list['columns']);
                                    }
                                }
                                echo $colCount;
                            ?>
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
        
        <div class="direct-access-info">
            <p><strong>Accès direct :</strong> Vous pouvez aussi accéder directement à une liste en utilisant l'URL : <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . url('user/list.php?id=X')); ?></code></p>
            <p>Remplacez X par l'ID de la liste.</p>
        </div>
    <?php endif; ?>
    
    <div class="mt-2">
        <a href="<?php echo url('user/?logout=1'); ?>" class="btn btn-secondary">Changer de nom</a>
    </div>
</div>

<style>
    .direct-access-info {
        margin-top: 30px;
        padding: 15px;
        background-color: #f0f0f0;
        border-radius: 5px;
        font-size: 14px;
    }
    .direct-access-info code {
        background-color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>

<?php
include __DIR__ . '/../includes/footer.php';

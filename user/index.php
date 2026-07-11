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
            // Charger toutes les inscriptions pour vérifier les noms existants
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
            
            // Vérifier aussi dans les cookies/sessions
            if (!$nameExists) {
                // Vérifier si un autre utilisateur utilise ce nom dans la session
                // (on ne peut pas vérifier les sessions des autres utilisateurs, mais on peut vérifier les cookies)
                if (isset($_COOKIE['user_name']) && $_COOKIE['user_name'] === $newUserName) {
                    $nameExists = true;
                }
            }
            
            if ($nameExists) {
                $warning = '⚠️ Ce nom est déjà utilisé par un autre utilisateur. Vous pouvez tout de même l\'utiliser, mais vous serez identifié avec le même nom.';
            }
            
            // Toujours accepter le nom, même s'il est déjà utilisé
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
    <?php endif; ?>
    
    <div class="mt-2">
        <a href="<?php echo url('user/?logout=1'); ?>" class="btn btn-secondary">Changer de nom</a>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

<?php
// ============================================
// Partie Utilisateur - Liste des événements disponibles
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Gérer la déconnexion (supprime toutes les sessions de nom)
if (isset($_GET['logout'])) {
    // Supprimer toutes les sessions de nom pour les listes
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'list_user_name_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    setcookie('user_name', '', time() - 3600, '/');
    redirect(url('user/index.php'));
}

// Charger les listes
$lists = loadLists();

// Filtrer pour ne garder que les listes actives et visibles
$visibleLists = [];
foreach ($lists as $list) {
    if (($list['is_active'] ?? true) && ($list['is_visible'] ?? true)) {
        $visibleLists[] = $list;
    }
}

// Si une seule liste visible existe, rediriger directement vers elle
if (count($visibleLists) === 1) {
    redirect(url('user/list.php?id=' . $visibleLists[0]['id']));
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Liste des événements disponibles</h1>
    <p>Sélectionnez une liste pour vous inscrire :</p>
    
    <?php if (empty($visibleLists)): ?>
        <div class="alert alert-info">
            Aucune liste n'est disponible pour le moment. Veuillez revenir plus tard.
        </div>
    <?php else: ?>
        <div class="list-grid">
            <?php foreach ($visibleLists as $list): ?>
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
                        <?php if ($list['is_readonly'] ?? false): ?>
                            <p><span class="badge badge-info">Lecture seule</span></p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?php echo url('user/list.php?id=' . $list['id']); ?>" class="btn btn-primary">
                        Voir la liste
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="direct-access-info">
            <p><strong>Accès direct :</strong> Vous pouvez aussi accéder directement à une liste en utilisant l'URL : <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . url('user/list.php?id=X')); ?></code></p>
            <p>Remplacez X par l'ID de la liste. <strong>Vous devrez entrer votre nom à chaque liste.</strong></p>
        </div>
    <?php endif; ?>
    
    <div class="mt-2">
        <a href="<?php echo url('user/?logout=1'); ?>" class="btn btn-secondary">Réinitialiser les noms</a>
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

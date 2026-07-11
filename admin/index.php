<?php
// ============================================
// Tableau de bord Administrateur
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect(url('admin/login.php'));
}

// Charger les listes
$lists = loadLists();

// Gérer la déconnexion
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    redirect(url('admin/login.php'));
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1>Tableau de bord Administrateur</h1>
        <div>
            <a href="<?php echo url('admin/?logout=1'); ?>" class="btn btn-secondary">Déconnexion</a>
        </div>
    </div>
    
    <p>Bonjour, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong> !</p>
    
    <div class="admin-nav">
        <a href="<?php echo url('admin/manage.php'); ?>" class="btn btn-primary">Gérer les listes</a>
        <a href="<?php echo url('admin/import.php'); ?>" class="btn btn-primary">Importer une liste</a>
        <a href="<?php echo url('admin/export.php'); ?>" class="btn btn-primary">Exporter une liste</a>
    </div>
    
    <h2>Liste des événements</h2>
    
    <?php if (empty($lists)): ?>
        <div class="alert alert-info">
            Aucune liste n'a encore été créée. <a href="<?php echo url('admin/manage.php'); ?>">Créer une nouvelle liste</a>.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Nombre de colonnes</th>
                        <th>Mot de passe</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lists as $list): ?>
                        <tr>
                            <td><?php echo $list['id']; ?></td>
                            <td><?php echo htmlspecialchars($list['name']); ?></td>
                            <td><?php echo count($list['columns']); ?></td>
                            <td>
                                <?php echo !empty($list['password']) ? '<span class="badge badge-warning">Oui</span>' : '<span class="badge badge-success">Non</span>'; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($list['created_at'])); ?></td>
                            <td>
                                <a href="<?php echo url('admin/manage.php?edit=' . $list['id']); ?>" class="btn btn-small btn-edit">Modifier</a>
                                <a href="<?php echo url('admin/manage.php?delete=' . $list['id']); ?>" class="btn btn-small btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette liste ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

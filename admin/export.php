<?php
// ============================================
// Export de listes au format CSV
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect(url('admin/login.php');
}

// Charger les listes
$lists = loadLists();

// Gérer l'export
if (isset($_GET['list_id']) && is_numeric($_GET['list_id'])) {
    $listId = (int)$_GET['list_id'];
    $csvContent = exportListToCSV($listId);
    
    if ($csvContent !== false) {
        $list = getListById($listId);
        $filename = 'export_' . sanitizeFilename($list['name']) . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        echo $csvContent;
        exit();
    } else {
        $error = 'Erreur lors de l\'export de la liste.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1>Exporter une liste</h1>
        <div>
            <a href="<?php echo url('admin/index.php" class="btn btn-secondary">← Retour au tableau de bord</a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <p>Sélectionnez une liste à exporter au format CSV :</p>
    
    <?php if (empty($lists)): ?>
        <div class="alert alert-info">
            Aucune liste n'est disponible pour l'export. <a href="<?php echo url('admin/manage.php">Créer une nouvelle liste</a>.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Nombre de colonnes</th>
                        <th>Nombre d'inscriptions</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lists as $list): ?>
                        <tr>
                            <td><?php echo $list['id']; ?></td>
                            <td><?php echo htmlspecialchars($list['name']); ?></td>
                            <td><?php echo count($list['columns']); ?></td>
                            <td>
                                <?php
                                $registrations = getAllRegistrations($list['id']);
                                echo count($registrations);
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo url('admin/export.php?list_id=<?php echo $list['id']; ?>" class="btn btn-small btn-download">
                                    Exporter CSV
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-2">
            <p>
                <strong>Format du CSV exporté :</strong><br>

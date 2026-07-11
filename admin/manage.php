<?php
// ============================================
// Gestion des listes (Créer/Modifier/Supprimer)
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect(BASE_URL . 'admin/login.php');
}

// Variables
$lists = loadLists();
$listToEdit = null;
$error = '';
$success = '';

// Gérer la suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $listId = (int)$_GET['delete'];
    
    // Vérifier le token CSRF
    if (!isset($_GET['csrf_token']) || !validateCsrfToken($_GET['csrf_token'])) {
        $error = 'Token de sécurité invalide.';
    } elseif (deleteList($listId)) {
        $success = 'Liste supprimée avec succès.';
    } else {
        $error = 'Erreur lors de la suppression de la liste.';
    }
    
    redirect(BASE_URL . 'admin/manage.php?message=' . urlencode($success ?: $error));
}

// Gérer l'édition
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $listToEdit = getListById((int)$_GET['edit']);
    if (!$listToEdit) {
        redirect(BASE_URL . 'admin/manage.php');
    }
}

// Gérer la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Token de sécurité invalide.';
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $listId = isset($_POST['list_id']) ? (int)$_POST['list_id'] : 0;
        $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $columnsInput = isset($_POST['columns']) ? trim($_POST['columns']) : '';
        
        // Convertir les colonnes en tableau
        $columns = [];
        if (!empty($columnsInput)) {
            $columns = array_map('trim', explode(",", $columnsInput));
            $columns = array_filter($columns); // Supprimer les entrées vides
        }
        
        // Validation
        if (empty($name)) {
            $error = 'Le nom de la liste est obligatoire.';
        } elseif (empty($columns)) {
            $error = 'Au moins une colonne est obligatoire.';
        } else {
            if ($action === 'add') {
                // Ajouter une nouvelle liste
                $newId = addList($name, $columns, $password, $description);
                if ($newId) {
                    $success = 'Liste créée avec succès !';
                    $listToEdit = null; // Réinitialiser le formulaire
                } else {
                    $error = 'Erreur lors de la création de la liste.';
                }
            } elseif ($action === 'edit' && $listId > 0) {
                // Modifier une liste existante
                if (updateList($listId, $name, $columns, $password, $description)) {
                    $success = 'Liste mise à jour avec succès !';
                    $listToEdit = getListById($listId); // Recharger les données
                } else {
                    $error = 'Erreur lors de la mise à jour de la liste.';
                }
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1><?php echo $listToEdit ? 'Modifier une liste' : 'Gérer les listes'; ?></h1>
        <div>
            <a href="<?php echo BASE_URL; ?>admin/index.php" class="btn btn-secondary">← Retour au tableau de bord</a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$listToEdit): ?>
        <!-- Liste des listes existantes -->
        <h2>Listes existantes</h2>
        
        <?php if (empty($lists)): ?>
            <div class="alert alert-info">
                Aucune liste n'existe encore.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Colonnes</th>
                            <th>Mot de passe</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lists as $list): ?>
                            <tr>
                                <td><?php echo $list['id']; ?></td>
                                <td><?php echo htmlspecialchars($list['name']); ?></td>
                                <td><?php echo !empty($list['description']) ? nl2br(htmlspecialchars(substr($list['description'], 0, 50)) . (strlen($list['description']) > 50 ? '...' : '')) : '<span class="badge badge-info">Aucune</span>'; ?></td>
                                <td><?php echo implode(', ', array_map('htmlspecialchars', array_slice($list['columns'], 0, 3))) . (count($list['columns']) > 3 ? '...' : ''); ?></td>
                                <td>
                                    <?php echo !empty($list['password']) ? '<span class="badge badge-warning">Oui</span>' : '<span class="badge badge-success">Non</span>'; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/manage.php?edit=<?php echo $list['id']; ?>" class="btn btn-small btn-edit">Modifier</a>
                                    <a href="<?php echo BASE_URL; ?>admin/manage.php?delete=<?php echo $list['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" class="btn btn-small btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette liste ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <hr>
        
        <!-- Formulaire pour ajouter une nouvelle liste -->
        <h2>Ajouter une nouvelle liste</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="name">Nom de la liste *</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optionnelle)</label>
                <textarea id="description" name="description" rows="2" placeholder="Ex: Liste pour le barbecue du 15 juillet"></textarea>
                <small>Description qui sera affichée aux utilisateurs.</small>
            </div>
            
            <div class="form-group">
                <label for="columns">Colonnes (séparées par des virgules) *</label>
                <textarea id="columns" name="columns" rows="3" placeholder="Ex: Ligne 1, Ligne 2, Ligne 3" required></textarea>
                <small>Séparez chaque colonne par une virgule. Utilisez l'import Markdown pour une structure plus complexe.</small>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe (optionnel)</label>
                <input type="text" id="password" name="password" placeholder="Laisser vide pour aucun mot de passe">
                <small>Si vous définissez un mot de passe, les utilisateurs devront le saisir pour accéder à cette liste.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Créer la liste</button>
            <button type="reset" class="btn btn-secondary">Réinitialiser</button>
        </form>
        
        <div class="import-link">
            <p>Pour importer une liste depuis un fichier CSV ou Markdown : <a href="<?php echo BASE_URL; ?>admin/import.php" class="btn btn-small btn-primary">Aller à l'import</a></p>
        </div>
        
    <?php else: ?>
        <!-- Formulaire pour modifier une liste -->
        <h2>Modifier : <?php echo htmlspecialchars($listToEdit['name']); ?></h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="list_id" value="<?php echo $listToEdit['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="name">Nom de la liste *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($listToEdit['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optionnelle)</label>
                <textarea id="description" name="description" rows="2" placeholder="Ex: Liste pour le barbecue du 15 juillet"><?php echo htmlspecialchars($listToEdit['description'] ?? ''); ?></textarea>
                <small>Description qui sera affichée aux utilisateurs.</small>
            </div>
            
            <div class="form-group">
                <label for="columns">Colonnes (séparées par des virgules) *</label>
                <textarea id="columns" name="columns" rows="3" required><?php echo htmlspecialchars(implode(', ', $listToEdit['columns'])); ?></textarea>
                <small>Séparez chaque colonne par une virgule.</small>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe (optionnel)</label>
                <input type="text" id="password" name="password" placeholder="Laisser vide pour supprimer le mot de passe" value="<?php echo htmlspecialchars($listToEdit['password'] ?? ''); ?>">
                <small>Si vous voulez supprimer le mot de passe, laissez ce champ vide.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?php echo BASE_URL; ?>admin/manage.php" class="btn btn-secondary">Annuler</a>
        </form>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

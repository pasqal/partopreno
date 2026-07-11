<?php
// ============================================
// Gestion des listes (Créer/Modifier/Supprimer)
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect(url('admin/login.php'));
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
    
    redirect(url('admin/manage.php?message=' . urlencode($success ?: $error)));
}

// Gérer l'édition
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $listToEdit = getListById((int)$_GET['edit']);
    if (!$listToEdit) {
        redirect(url('admin/manage.php'));
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
        
        // Récupérer les colonnes et catégories depuis le formulaire
        $columns = [];
        $categories = isset($_POST['category']) ? $_POST['category'] : [];
        $columnNames = isset($_POST['column_name']) ? $_POST['column_name'] : [];
        
        // Si on utilise l'ancien format (une seule textarea)
        if (isset($_POST['columns']) && !empty(trim($_POST['columns']))) {
            $columnsInput = trim($_POST['columns']);
            $columns = array_map('trim', explode(",", $columnsInput));
            $columns = array_filter($columns);
        } else {
            // Nouveau format avec catégories
            foreach ($columnNames as $index => $colName) {
                $colName = trim($colName);
                if (!empty($colName)) {
                    $category = isset($categories[$index]) ? trim($categories[$index]) : '';
                    $columns[] = [
                        'name' => $colName,
                        'category' => $category
                    ];
                }
            }
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
            <a href="<?php echo url('admin/index.php'); ?>" class="btn btn-secondary">← Retour au tableau de bord</a>
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
                            <th>Catégories</th>
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
                                <td><?php 
                                    $colCount = 0;
                                    if (!empty($list['columns'])) {
                                        if (isset($list['columns'][0]['name'])) {
                                            $colCount = count($list['columns']);
                                        } else {
                                            $colCount = count($list['columns']);
                                        }
                                    }
                                    echo $colCount;
                                    ?></td>
                                <td><?php 
                                    $catCount = 0;
                                    if (!empty($list['columns']) && isset($list['columns'][0]['category'])) {
                                        $categories = array_unique(array_column($list['columns'], 'category'));
                                        $catCount = count(array_filter($categories));
                                        echo $catCount > 0 ? $catCount : 'Aucune';
                                    } else {
                                        echo 'Aucune';
                                    }
                                    ?></td>
                                <td>
                                    <?php echo !empty($list['password']) ? '<span class="badge badge-warning">Oui</span>' : '<span class="badge badge-success">Non</span>'; ?>
                                </td>
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
                <label>Colonnes avec catégories *</label>
                <div id="columns-container">
                    <div class="column-row">
                        <input type="text" name="category[]" placeholder="Catégorie (optionnelle)" style="width: 30%; margin-right: 10px;">
                        <input type="text" name="column_name[]" placeholder="Nom de la colonne *" style="width: 60%;" required>
                        <button type="button" class="btn btn-small btn-delete" onclick="removeColumnRow(this)">×</button>
                    </div>
                </div>
                <button type="button" class="btn btn-small btn-primary" onclick="addColumnRow()" style="margin-top: 10px;">+ Ajouter une colonne</button>
                <small>Laissez la catégorie vide pour les colonnes sans catégorie.</small>
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
            <p>Pour importer une liste depuis un fichier CSV ou Markdown : <a href="<?php echo url('admin/import.php'); ?>" class="btn btn-small btn-primary">Aller à l'import</a></p>
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
                <label>Colonnes avec catégories *</label>
                <div id="columns-container">
                    <?php
                    // Si l'ancienne structure (tableau simple)
                    if (!empty($listToEdit['columns']) && !isset($listToEdit['columns'][0]['name'])) {
                        foreach ($listToEdit['columns'] as $col) {
                            echo '<div class="column-row">';
                            echo '<input type="text" name="category[]" placeholder="Catégorie" style="width: 30%; margin-right: 10px;" value="">';
                            echo '<input type="text" name="column_name[]" placeholder="Nom de la colonne *" style="width: 60%;" value="' . htmlspecialchars($col) . '" required>';
                            echo '<button type="button" class="btn btn-small btn-delete" onclick="removeColumnRow(this)">×</button>';
                            echo '</div>';
                        }
                    } elseif (!empty($listToEdit['columns'])) {
                        foreach ($listToEdit['columns'] as $col) {
                            echo '<div class="column-row">';
                            echo '<input type="text" name="category[]" placeholder="Catégorie" style="width: 30%; margin-right: 10px;" value="' . htmlspecialchars($col['category'] ?? '') . '">';
                            echo '<input type="text" name="column_name[]" placeholder="Nom de la colonne *" style="width: 60%;" value="' . htmlspecialchars($col['name']) . '" required>';
                            echo '<button type="button" class="btn btn-small btn-delete" onclick="removeColumnRow(this)">×</button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <button type="button" class="btn btn-small btn-primary" onclick="addColumnRow()" style="margin-top: 10px;">+ Ajouter une colonne</button>
                <small>Laissez la catégorie vide pour les colonnes sans catégorie.</small>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe (optionnel)</label>
                <input type="text" id="password" name="password" placeholder="Laisser vide pour supprimer le mot de passe" value="<?php echo htmlspecialchars($listToEdit['password'] ?? ''); ?>">
                <small>Si vous voulez supprimer le mot de passe, laissez ce champ vide.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?php echo url('admin/manage.php'); ?>" class="btn btn-secondary">Annuler</a>
        </form>
    <?php endif; ?>
</div>

<script>
// Fonctions pour gérer les lignes de colonnes dynamiquement
function addColumnRow() {
    const container = document.getElementById('columns-container');
    const row = document.createElement('div');
    row.className = 'column-row';
    row.innerHTML = `
        <input type="text" name="category[]" placeholder="Catégorie (optionnelle)" style="width: 30%; margin-right: 10px;">
        <input type="text" name="column_name[]" placeholder="Nom de la colonne *" style="width: 60%;" required>
        <button type="button" class="btn btn-small btn-delete" onclick="removeColumnRow(this)">×</button>
    `;
    container.appendChild(row);
}

function removeColumnRow(button) {
    const row = button.parentElement;
    const container = document.getElementById('columns-container');
    if (container.children.length > 1) {
        container.removeChild(row);
    } else {
        alert('Vous devez avoir au moins une colonne.');
    }
}
</script>

<style>
.column-row {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}
.column-row input {
    padding: 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<?php
include __DIR__ . '/../includes/footer.php';

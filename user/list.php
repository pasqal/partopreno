<?php
// ============================================
// Partie Utilisateur - Tableau d'inscription
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

// Vérifier l'accès à la liste
if (!empty($list['password']) && !isset($_SESSION['list_access_' . $listId])) {
    redirect(url('user/auth.php?id=' . $listId));
}

// Vérifier si l'utilisateur a un nom
$userName = getCurrentUserName();
if (empty($userName)) {
    redirect(url('user/index.php'));
}

// Gérer les inscriptions/désinscriptions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['column'])) {
        // Clic sur une case : ajouter/supprimer l'inscription
        $column = sanitizeInput($_POST['column']);
        // Vérifier que la colonne existe dans la liste
        $columnExists = false;
        foreach ($list['columns'] as $col) {
            $colName = is_array($col) ? $col['name'] : $col;
            if ($colName === $column) {
                $columnExists = true;
                break;
            }
        }
        if ($columnExists) {
            registerUser($listId, $userName, $column);
        }
    } elseif (isset($_POST['remove_column'])) {
        // Clic sur une ligne : supprimer l'inscription
        $column = sanitizeInput($_POST['remove_column']);
        $columnExists = false;
        foreach ($list['columns'] as $col) {
            $colName = is_array($col) ? $col['name'] : $col;
            if ($colName === $column) {
                $columnExists = true;
                break;
            }
        }
        if ($columnExists) {
            registerUser($listId, $userName, $column);
        }
    }
    redirect(url('user/list.php?id=' . $listId));
}

// Charger les inscriptions
$registrations = getAllRegistrations($listId);
$userRegistrations = isset($registrations[$userName]) ? $registrations[$userName] : [];

// Organiser les colonnes par catégorie
$columnsByCategory = getColumnsByCategory($list);

// Fonction pour générer une couleur unique pour un utilisateur
function getUserColor($username) {
    // Générer une couleur basée sur le nom d'utilisateur pour qu'elle soit toujours la même
    $colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2',
        '#F8B739', '#52B788', '#FF9AA2', '#5DADE2', '#F4D03F'
    ];
    
    // Utiliser le hash du nom pour sélectionner une couleur
    $hash = crc32($username);
    $index = abs($hash) % count($colors);
    return $colors[$index];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1><?php echo htmlspecialchars($list['name']); ?></h1>
        <div class="user-info">
            <span>Connecté en tant que : <strong><?php echo htmlspecialchars($userName); ?></strong></span>
        </div>
    </div>
    
    <p>
        <a href="<?php echo url('user/index.php'); ?>" class="btn btn-secondary">← Retour à la liste des événements</a>
    </p>
    
    <?php if (!empty($list['description'])): ?>
        <div class="list-description">
            <?php echo nl2br(htmlspecialchars($list['description'])); ?>
        </div>
    <?php endif; ?>
    
    <div class="registration-container">
        <?php
        // Afficher chaque catégorie
        foreach ($columnsByCategory as $category => $columns):
            // Afficher le titre de la catégorie si elle existe
            if (!empty($category)):
                echo '<h3 class="category-title">' . htmlspecialchars($category) . '</h3>';
            endif;
            
            // Tableau pour cette catégorie
            echo '<table class="registration-table">';
            echo '<tbody>';
            
            foreach ($columns as $col):
                $columnName = $col['name'];
                $isRegistered = in_array($columnName, $userRegistrations);
                $usersInColumn = [];
                
                // Récupérer tous les utilisateurs inscrits à cette colonne
                foreach ($registrations as $name => $userCols) {
                    if (in_array($columnName, $userCols)) {
                        $usersInColumn[] = $name;
                    }
                }
                
                echo '<tr class="column-row" data-column="' . htmlspecialchars($columnName) . '">';
                
                // Cellule de l'entrée (colonne)
                echo '<td class="column-name">';
                echo '<form method="post" action="' . url('user/list.php?id=' . $listId) . '" style="margin: 0;">';
                echo '<input type="hidden" name="remove_column" value="' . htmlspecialchars($columnName) . '">';
                echo '<button type="submit" class="column-name-btn" style="background: none; border: none; text-align: left; width: 100%; cursor: pointer;">';
                echo htmlspecialchars($columnName);
                echo '</button>';
                echo '</form>';
                echo '</td>';
                
                // Cellule des inscriptions
                echo '<td class="users-cell">';
                
                // Case à cocher pour s'inscrire
                echo '<form method="post" action="' . url('user/list.php?id=' . $listId) . '" style="margin: 0; display: inline-block;">';
                echo '<input type="hidden" name="column" value="' . htmlspecialchars($columnName) . '">';
                echo '<button type="submit" class="cell-btn ' . ($isRegistered ? 'registered' : '') . '">';
                echo $isRegistered ? '✓' : '+';
                echo '</button>';
                echo '</form>';
                
                // Afficher les bulles avec les noms des utilisateurs inscrits
                if (!empty($usersInColumn)):
                    echo '<div class="users-bubbles">';
                    foreach ($usersInColumn as $name):
                        $isCurrentUser = ($name === $userName);
                        $userColor = getUserColor($name);
                        echo '<span class="user-bubble" style="background-color: ' . $userColor . ';">' . htmlspecialchars($name) . '</span>';
                    endforeach;
                    echo '</div>';
                endif;
                
                echo '</td>';
                echo '</tr>';
            endforeach;
            
            echo '</tbody>';
            echo '</table>';
        endforeach;
        ?>
    </div>
    
    <div class="mt-2 legend">
        <p><strong>Légende :</strong></p>
        <ul>
            <li><span class="legend-mark registered">✓</span> = Vous êtes inscrit</li>
            <li><span class="legend-mark">+</span> = Cliquez pour vous inscrire</li>
            <li><span class="user-bubble">Nom</span> = Utilisateur inscrit (chaque utilisateur a sa propre couleur)</li>
        </ul>
    </div>
</div>

<style>
    .registration-container { margin: 20px 0; }
    .registration-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .registration-table td { padding: 12px 15px; border: 1px solid #ddd; vertical-align: middle; }
    .column-name { font-weight: bold; width: 60%; background-color: #f8f9fa; }
    .column-name-btn:hover { background-color: #e9ecef; }
    .users-cell { width: 40%; }
    .cell-btn {
        background: none; border: 2px solid #4a6fa5; border-radius: 50%;
        width: 30px; height: 30px; cursor: pointer; font-size: 16px;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: 10px;
    }
    .cell-btn.registered { background-color: #4a6fa5; color: white; border-color: #4a6fa5; }
    .cell-btn:hover { background-color: #e9ecef; }
    .users-bubbles { display: inline-block; margin-left: 10px; }
    .user-bubble {
        display: inline-block; color: white;
        padding: 4px 10px; border-radius: 15px; margin: 3px; font-size: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .user-bubble.current-user { 
        background-color: #28a745 !important;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #28a745;
    }
    .column-row:hover { background-color: #f8f9fa; }
    .legend { margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
    .legend ul { list-style: none; padding: 0; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
    .legend li { display: flex; align-items: center; gap: 5px; }
    .legend-mark {
        display: inline-block; padding: 2px 6px; border: 2px solid #4a6fa5;
        border-radius: 50%; font-size: 14px;
    }
    .legend-mark.registered { background-color: #4a6fa5; color: white; }
    .category-title {
        color: #4a6fa5;
        border-bottom: 2px solid #4a6fa5;
        padding-bottom: 8px;
        margin: 20px 0 10px 0;
    }
</style>

<?php
include __DIR__ . '/../includes/footer.php';

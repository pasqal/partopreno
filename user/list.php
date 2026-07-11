<?php
// ============================================
// Partie Utilisateur - Tableau d'inscription
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

// Vérifier l'accès à la liste
if (!empty($list['password']) && !isset($_SESSION['list_access_' . $listId])) {
    redirect(BASE_URL . 'user/auth.php?id=' . $listId);
}

// Vérifier si l'utilisateur a un nom
$userName = getCurrentUserName();
if (empty($userName)) {
    redirect(BASE_URL . 'user/index.php');
}

// Gérer les inscriptions/désinscriptions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['column'])) {
        // Clic sur une case : ajouter/supprimer l'inscription
        $column = sanitizeInput($_POST['column']);
        if (in_array($column, $list['columns'])) {
            registerUser($listId, $userName, $column);
        }
    } elseif (isset($_POST['remove_column'])) {
        // Clic sur une ligne : supprimer l'inscription
        $column = sanitizeInput($_POST['remove_column']);
        if (in_array($column, $list['columns'])) {
            registerUser($listId, $userName, $column);
        }
    }
    redirect(BASE_URL . 'user/list.php?id=' . $listId);
}

// Charger les inscriptions
$registrations = getAllRegistrations($listId);
$userRegistrations = isset($registrations[$userName]) ? $registrations[$userName] : [];

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
        <a href="<?php echo BASE_URL; ?>user/index.php" class="btn btn-secondary">← Retour à la liste des événements</a>
    </p>
    
    <?php if (!empty($list['description'])): ?>
        <div class="list-description">
            <?php echo nl2br(htmlspecialchars($list['description'])); ?>
        </div>
    <?php endif; ?>
    
    <div class="registration-container">
        <table class="registration-table">
            <tbody>
                <?php
                // Afficher chaque colonne comme une ligne
                foreach ($list['columns'] as $index => $column):
                    $isRegistered = in_array($column, $userRegistrations);
                    $usersInColumn = [];
                    
                    // Récupérer tous les utilisateurs inscrits à cette colonne
                    foreach ($registrations as $name => $userCols) {
                        if (in_array($column, $userCols)) {
                            $usersInColumn[] = htmlspecialchars($name);
                        }
                    }
                    
                    // Déterminer si on affiche un séparateur de titre (toutes les 5 lignes par exemple)
                    $showTitleSeparator = ($index % 5 == 0 && $index > 0);
                    
                    if ($showTitleSeparator):
                        echo '<tr class="title-separator"><td colspan="2"><hr></td></tr>';
                    
                    echo '<tr class="column-row" data-column="' . htmlspecialchars($column) . '">';
                    
                    // Cellule de l'entrée (colonne)
                    echo '<td class="column-name">';
                    echo '<form method="post" action="' . BASE_URL . 'user/list.php?id=' . $listId . '" style="margin: 0;">';
                    echo '<input type="hidden" name="remove_column" value="' . htmlspecialchars($column) . '">';
                    echo '<button type="submit" class="column-name-btn" style="background: none; border: none; text-align: left; width: 100%; cursor: pointer;">';
                    echo htmlspecialchars($column);
                    echo '</button>';
                    echo '</form>';
                    echo '</td>';
                    
                    // Cellule des inscriptions
                    echo '<td class="users-cell">';
                    
                    // Case à cocher pour s'inscrire
                    echo '<form method="post" action="' . BASE_URL . 'user/list.php?id=' . $listId . '" style="margin: 0; display: inline-block;">';
                    echo '<input type="hidden" name="column" value="' . htmlspecialchars($column) . '">';
                    echo '<button type="submit" class="cell-btn ' . ($isRegistered ? 'registered' : '') . '">';
                    echo $isRegistered ? '✓' : '+';
                    echo '</button>';
                    echo '</form>';
                    
                    // Afficher les bulles avec les noms des utilisateurs inscrits
                    if (!empty($usersInColumn)):
                        echo '<div class="users-bubbles">';
                        foreach ($usersInColumn as $name):
                            $isCurrentUser = ($name === htmlspecialchars($userName));
                            echo '<span class="user-bubble ' . ($isCurrentUser ? 'current-user' : '') . '">' . $name . '</span>';
                        endforeach;
                        echo '</div>';
                    endif;
                    
                    echo '</td>';
                    echo '</tr>';
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-2 legend">
        <p><strong>Légende :</strong></p>
        <ul>
            <li><span class="legend-mark registered">✓</span> = Vous êtes inscrit</li>
            <li><span class="legend-mark">+</span> = Cliquez pour vous inscrire</li>
            <li><span class="user-bubble">Nom</span> = Utilisateur inscrit</li>
        </ul>
    </div>
</div>

<style>
    .registration-container {
        margin: 20px 0;
    }
    
    .registration-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .registration-table td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        vertical-align: middle;
    }
    
    .column-name {
        font-weight: bold;
        width: 60%;
        background-color: #f8f9fa;
    }
    
    .column-name-btn:hover {
        background-color: #e9ecef;
    }
    
    .users-cell {
        width: 40%;
    }
    
    .cell-btn {
        background: none;
        border: 2px solid #4a6fa5;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    
    .cell-btn.registered {
        background-color: #4a6fa5;
        color: white;
        border-color: #4a6fa5;
    }
    
    .cell-btn:hover {
        background-color: #e9ecef;
    }
    
    .users-bubbles {
        display: inline-block;
        margin-left: 10px;
    }
    
    .user-bubble {
        display: inline-block;
        background-color: #4a6fa5;
        color: white;
        padding: 4px 10px;
        border-radius: 15px;
        margin: 3px;
        font-size: 12px;
    }
    
    .user-bubble.current-user {
        background-color: #28a745;
    }
    
    .title-separator hr {
        border: none;
        border-top: 2px solid #ddd;
        margin: 10px 0;
    }
    
    .column-row:hover {
        background-color: #f8f9fa;
    }
    
    .legend {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    
    .legend ul {
        list-style: none;
        padding: 0;
        display: flex;
        gap: 20px;
        align-items: center;
    }
    
    .legend li {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .legend-mark {
        display: inline-block;
        padding: 2px 6px;
        border: 2px solid #4a6fa5;
        border-radius: 50%;
        font-size: 14px;
    }
    
    .legend-mark.registered {
        background-color: #4a6fa5;
        color: white;
    }
</style>

<?php
include __DIR__ . '/../includes/footer.php';

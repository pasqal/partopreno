<?php
// ============================================
// Partie Utilisateur - Tableau d'inscription
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'ID de la liste est fourni
if (!isset($_GET['id'])) {
    redirect('index.php');
}

$listId = (int)$_GET['id'];
$list = getListById($listId);

if (!$list) {
    redirect('index.php');
}

// Vérifier l'accès à la liste
if (!empty($list['password']) && !isset($_SESSION['list_access_' . $listId])) {
    redirect('auth.php?id=' . $listId);
}

// Vérifier si l'utilisateur a un nom
$userName = getCurrentUserName();
if (empty($userName)) {
    redirect('index.php');
}

// Gérer les inscriptions/désinscriptions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['column'])) {
    $column = sanitizeInput($_POST['column']);
    
    // Vérifier que la colonne existe dans la liste
    if (in_array($column, $list['columns'])) {
        registerUser($listId, $userName, $column);
    }
    
    // Rediriger pour éviter le re-post
    redirect('list.php?id=' . $listId);
}

// Charger les inscriptions
$registrations = getAllRegistrations($listId);

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
        <a href="index.php" class="btn btn-secondary">← Retour à la liste des événements</a>
    </p>
    
    <div class="table-container">
        <table class="registration-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <?php foreach ($list['columns'] as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Afficher les autres utilisateurs
                foreach ($registrations as $name => $userColumns):
                    if ($name !== $userName):
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($name) . '</td>';
                        foreach ($list['columns'] as $column):
                            $isRegistered = in_array($column, $userColumns);
                            echo '<td>';
                            if ($isRegistered) {
                                echo '<span class="registered-mark">✓</span>';
                            }
                            echo '</td>';
                        endforeach;
                        echo '</tr>';
                    endif;
                endforeach;
                
                // Ligne de l'utilisateur actuel
                echo '<tr class="current-user-row">';
                echo '<td><strong>' . htmlspecialchars($userName) . '</strong></td>';
                foreach ($list['columns'] as $column):
                    $isRegistered = isset($registrations[$userName]) && in_array($column, $registrations[$userName]);
                    echo '<td>';
                    echo '<form method="post" action="" style="margin: 0;">';
                    echo '<input type="hidden" name="column" value="' . htmlspecialchars($column) . '">';
                    echo '<button type="submit" class="cell-btn ' . ($isRegistered ? 'registered' : '') . '">';
                    echo $isRegistered ? '✓' : '+';
                    echo '</button>';
                    echo '</form>';
                    echo '</td>';
                endforeach;
                echo '</tr>';
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-2">
        <p><strong>Légende :</strong></p>
        <ul>
            <li><span class="legend-mark registered">✓</span> = Inscrit</li>
            <li><span class="legend-mark">+</span> = Cliquez pour vous inscrire</li>
        </ul>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

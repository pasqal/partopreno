<?php
// ============================================
// En-tête commun à toutes les pages
// ============================================

require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        // Déterminer le titre en fonction de la page
        $page = basename($_SERVER['PHP_SELF']);
        $titles = [
            'login.php' => 'Connexion Admin - Système d\'Inscription',
            'index.php' => 'Tableau de bord Admin - Système d\'Inscription',
            'manage.php' => 'Gérer les listes - Système d\'Inscription',
            'import.php' => 'Importer une liste - Système d\'Inscription',
            'export.php' => 'Exporter une liste - Système d\'Inscription',
        ];
        
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            echo $titles[$page] ?? 'Administration - Système d\'Inscription';
        } elseif (strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
            if ($page === 'index.php') {
                echo 'Liste des événements - Système d\'Inscription';
            } elseif ($page === 'list.php') {
                echo 'Tableau d\'inscription - Système d\'Inscription';
            } else {
                echo 'Système d\'Inscription';
            }
        } else {
            echo 'Système d\'Inscription';
        }
        ?>
    </title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234a6fa5'><path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/></svg>">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="../">
                    <span class="logo-text">Système d'Inscription</span>
                </a>
            </div>
            <nav class="nav">
                <?php if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false): ?>
                    <a href="../admin/" class="nav-link">Tableau de bord</a>
                    <a href="../admin/manage.php" class="nav-link">Gérer les listes</a>
                    <a href="../admin/import.php" class="nav-link">Importer</a>
                    <a href="../admin/export.php" class="nav-link">Exporter</a>
                    <a href="../admin/?logout=1" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <a href="../user/" class="nav-link">Accueil</a>
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <a href="../user/?logout=1" class="nav-link">Changer de nom</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main">

<?php
// ============================================
// Import de listes depuis CSV/TXT/Markdown
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/markdown_parser.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect(BASE_URL . 'admin/login.php');
}

// Variables
$error = '';
$success = '';

// Gérer l'import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Token de sécurité invalide.';
    } else {
        $file = $_FILES['file'];
        
        // Vérifier les erreurs de téléchargement
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Erreur lors du téléchargement du fichier : ' . 
                     [UPLOAD_ERR_INI_SIZE => 'Fichier trop grand (dépasse upload_max_filesize)',
                      UPLOAD_ERR_FORM_SIZE => 'Fichier trop grand (dépasse MAX_FILE_SIZE)',
                      UPLOAD_ERR_PARTIAL => 'Fichier partiellement téléchargé',
                      UPLOAD_ERR_NO_FILE => 'Aucun fichier téléchargé',
                      UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                      UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier',
                      UPLOAD_ERR_EXTENSION => 'Extension PHP a arrêté le téléchargement'][($file['error'] ?? 0)];
        } else {
            // Vérifier l'extension
            $allowedExtensions = ['csv', 'txt', 'md', 'markdown'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $error = 'Seuls les fichiers CSV, TXT et Markdown (.md, .markdown) sont autorisés.';
            } else {
                // Déplacer le fichier temporaire
                $tempPath = $file['tmp_name'];
                
                // Traiter selon l'extension
                if (in_array($fileExtension, ['md', 'markdown'])) {
                    $result = importListFromMarkdown($tempPath);
                } else {
                    $result = importListFromCSV($tempPath);
                }
                
                if ($result === false) {
                    $error = 'Erreur lors de l\'import du fichier. Vérifiez que le format est correct.';
                } else {
                    $success = 'Liste "' . htmlspecialchars($result['name']) . '" importée avec succès !';
                    $success .= '<br>ID : ' . $result['id'];
                    $success .= '<br>Colonnes : ' . implode(', ', array_map('htmlspecialchars', $result['columns']));
                    if (!empty($result['description'])) {
                        $success .= '<br><br><strong>Description :</strong><br>' . nl2br(htmlspecialchars($result['description']));
                    }
                }
                
                // Supprimer le fichier temporaire
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="header-flex">
        <h1>Importer une liste</h1>
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
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="import-info">
        <h3>Formats acceptés</h3>
        <p>Vous pouvez importer des listes depuis des fichiers <strong>CSV</strong>, <strong>TXT</strong> ou <strong>Markdown</strong>.</p>
        
        <hr>
        
        <h4>Format CSV/TXT</h4>
        <p>Le fichier doit avoir le format suivant (séparateur : virgule <code>,</code>) :</p>
        <pre><code>Nom de la liste,Nom1,Nom2,Nom3
Exemple,Ligne 1,Ligne 2,Ligne 3</code></pre>
        <ul>
            <li>La <strong>première ligne</strong> est ignorée (en-tête).</li>
            <li>La <strong>deuxième ligne</strong> contient le nom de la liste suivi des noms des colonnes.</li>
            <li>Les lignes suivantes seront ignorées.</li>
        </ul>
        
        <hr>
        
        <h4>Format Markdown</h4>
        <p>Le fichier Markdown doit avoir la structure suivante :</p>
        <pre><code># Titre de la liste
Description de la liste (optionnelle)

## Catégorie 1
- Élément 1
- Élément 2
- Élément 3

## Catégorie 2
- Élément A
- Élément B</code></pre>
        <ul>
            <li><code>#</code> : Titre de la liste (obligatoire)</li>
            <li>Les lignes suivant le titre (avant le premier <code>##</code>) deviennent la description</li>
            <li><code>##</code> : Nom de la catégorie (optionnelle, devient un préfixe pour les éléments)</li>
            <li><code>-</code> : Élément de la liste (devient une colonne du tableau)</li>
            <li>Si une catégorie est définie, les éléments seront préfixés par "Catégorie - Élément"</li>
        </ul>
        
        <div class="example-link">
            <p>Exemples de fichiers :</p>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>assets/examples/import_example.csv" target="_blank">Exemple CSV</a></li>
                <li><a href="<?php echo BASE_URL; ?>assets/examples/Match simple.md" target="_blank">Exemple Markdown 1</a></li>
                <li><a href="<?php echo BASE_URL; ?>assets/examples/liste2.md" target="_blank">Exemple Markdown 2</a></li>
            </ul>
        </div>
    </div>
    
    <hr>
    
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

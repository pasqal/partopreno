<?php
// ============================================
// Import de listes depuis CSV/TXT
// ============================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la connexion admin
if (!isAdminLoggedIn() || !checkSessionTimeout()) {
    redirect('login.php');
}

// Variables
$error = '';
$success = '';

// Gérer l'import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Token de sécurité invalide.';
    } else {
        $file = $_FILES['csv_file'];
        
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
            $allowedExtensions = ['csv', 'txt'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $error = 'Seuls les fichiers CSV et TXT sont autorisés.';
            } else {
                // Déplacer le fichier temporaire
                $tempPath = $file['tmp_name'];
                $result = importListFromCSV($tempPath);
                
                if ($result === false) {
                    $error = 'Erreur lors de l\'import du fichier. Vérifiez que le format est correct.';
                } else {
                    $success = 'Liste "' . htmlspecialchars($result['name']) . '" importée avec succès !';
                    $success .= '<br>ID : ' . $result['id'];
                    $success .= '<br>Colonnes : ' . implode(', ', array_map('htmlspecialchars', $result['columns']));
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
            <a href="index.php" class="btn btn-secondary">← Retour au tableau de bord</a>
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
        <h3>Format du fichier CSV/TXT</h3>
        <p>Le fichier doit avoir le format suivant (séparateur : virgule <code>,</code>) :</p>
        <pre><code>Nom de la liste,Nom1,Nom2,Nom3
Exemple,Ligne 1,Ligne 2,Ligne 3</code></pre>
        <ul>
            <li>La <strong>première ligne</strong> est ignorée (en-tête).</li>
            <li>La <strong>deuxième ligne</strong> contient le nom de la liste suivi des noms des colonnes.</li>
            <li>Les lignes suivantes seront ignorées.</li>
        </ul>
    </div>
    
    <hr>
    
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <div class="form-group">
            <label for="csv_file">Sélectionnez un fichier CSV ou TXT :</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Importer</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
    </form>
    
    <div class="mt-2">
        <p>
            <strong>Exemple de fichier valide :</strong><br>
            <a href="../assets/examples/import_example.csv" class="btn btn-small btn-download" download>Télécharger un exemple CSV</a>
        </p>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

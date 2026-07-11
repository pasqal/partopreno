<?php
// ============================================
// Fonctions utilitaires pour le système d'inscription
// ============================================

// NOTE: config.php doit être inclus AVANT ce fichier
// Si ce fichier est inclus directement sans config.php, on l'inclut
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

// ============================================
// Gestion des listes
// ============================================

/**
 * Charger la liste des événements/lignes depuis le fichier JSON
 * @return array Tableau des listes
 */
function loadLists() {
    if (!file_exists(LISTS_FILE)) {
        return [];
    }
    $content = file_get_contents(LISTS_FILE);
    $lists = json_decode($content, true);
    return is_array($lists) ? $lists : [];
}

/**
 * Sauvegarder la liste des événements/lignes dans le fichier JSON
 * @param array $lists Tableau des listes à sauvegarder
 * @return bool Succès ou échec
 */
function saveLists($lists) {
    $json = json_encode($lists, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(LISTS_FILE, $json) !== false;
}

/**
 * Obtenir une liste par son ID
 * @param int $id ID de la liste
 * @return array|null Liste trouvée ou null
 */
function getListById($id) {
    $lists = loadLists();
    foreach ($lists as $list) {
        if ($list['id'] == $id) {
            return $list;
        }
    }
    return null;
}

/**
 * Obtenir une liste par son nom
 * @param string $name Nom de la liste
 * @return array|null Liste trouvée ou null
 */
function getListByName($name) {
    $lists = loadLists();
    foreach ($lists as $list) {
        if ($list['name'] === $name) {
            return $list;
        }
    }
    return null;
}

/**
 * Normaliser les colonnes pour le stockage
 * Convertit les colonnes en tableau avec name et category
 * @param array|string $columns Colonnes à normaliser
 * @return array Tableau normalisé
 */
function normalizeColumns($columns) {
    $normalized = [];
    
    if (is_string($columns)) {
        // Ancien format: chaîne séparée par des virgules
        $items = array_map('trim', explode(",", $columns));
        foreach ($items as $item) {
            $normalized[] = ['name' => $item, 'category' => ''];
        }
    } elseif (is_array($columns)) {
        // Nouveau format: tableau de colonnes
        foreach ($columns as $col) {
            if (is_array($col) && isset($col['name'])) {
                // Déjà normalisé
                $normalized[] = [
                    'name' => $col['name'],
                    'category' => $col['category'] ?? ''
                ];
            } elseif (is_string($col)) {
                // Ancien format: chaîne simple
                $normalized[] = ['name' => $col, 'category' => ''];
            }
        }
    }
    
    return $normalized;
}

/**
 * Ajouter une nouvelle liste
 * @param string $name Nom de la liste
 * @param array $columns Tableau des colonnes (noms des lignes/événements)
 * @param string|null $password Mot de passe (optionnel)
 * @param string|null $description Description de la liste (optionnel)
 * @param bool $isVisible Si la liste est visible
 * @param bool $isReadOnly Si la liste est en lecture seule
 * @return int|false ID de la nouvelle liste ou false en cas d'erreur
 */
function addList($name, $columns, $password = null, $description = null, $isVisible = true, $isReadOnly = false) {
    $lists = loadLists();
    
    // Générer un ID unique
    $maxId = 0;
    foreach ($lists as $list) {
        if ($list['id'] > $maxId) {
            $maxId = $list['id'];
        }
    }
    $newId = $maxId + 1;
    
    // Normaliser les colonnes
    $normalizedColumns = normalizeColumns($columns);
    
    $newList = [
        'id' => $newId,
        'name' => $name,
        'password' => $password,
        'description' => $description,
        'columns' => $normalizedColumns,
        'is_visible' => $isVisible,
        'is_readonly' => $isReadOnly,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $lists[] = $newList;
    
    if (saveLists($lists)) {
        // Créer le fichier d'inscriptions pour cette liste
        $registrationFile = REGISTRATIONS_DIR . 'list_' . $newId . '.json';
        file_put_contents($registrationFile, '{}');
        return $newId;
    }
    
    return false;
}

/**
 * Mettre à jour une liste
 * @param int $id ID de la liste
 * @param string $name Nom de la liste
 * @param array $columns Tableau des colonnes
 * @param string|null $password Mot de passe (null pour ne pas changer)
 * @param string|null $description Description de la liste (null pour ne pas changer)
 * @param bool|null $isVisible Si la liste est visible (null pour ne pas changer)
 * @param bool|null $isReadOnly Si la liste est en lecture seule (null pour ne pas changer)
 * @return bool Succès ou échec
 */
function updateList($id, $name, $columns, $password = null, $description = null, $isVisible = null, $isReadOnly = null) {
    $lists = loadLists();
    $found = false;
    
    // Normaliser les colonnes
    $normalizedColumns = normalizeColumns($columns);
    
    foreach ($lists as &$list) {
        if ($list['id'] == $id) {
            $list['name'] = $name;
            $list['columns'] = $normalizedColumns;
            if ($password !== null) {
                $list['password'] = $password;
            }
            if ($description !== null) {
                $list['description'] = $description;
            }
            if ($isVisible !== null) {
                $list['is_visible'] = $isVisible;
            }
            if ($isReadOnly !== null) {
                $list['is_readonly'] = $isReadOnly;
            }
            $found = true;
            break;
        }
    }
    
    if ($found) {
        return saveLists($lists);
    }
    
    return false;
}

/**
 * Supprimer une liste
 * @param int $id ID de la liste
 * @return bool Succès ou échec
 */
function deleteList($id) {
    $lists = loadLists();
    $newLists = [];
    $found = false;
    
    foreach ($lists as $list) {
        if ($list['id'] == $id) {
            $found = true;
            // Supprimer aussi le fichier d'inscriptions
            $registrationFile = REGISTRATIONS_DIR . 'list_' . $id . '.json';
            if (file_exists($registrationFile)) {
                unlink($registrationFile);
            }
        } else {
            $newLists[] = $list;
        }
    }
    
    if ($found) {
        return saveLists($newLists);
    }
    
    return false;
}

// ============================================
// Gestion des inscriptions
// ============================================

/**
 * Charger les inscriptions pour une liste
 * @param int $listId ID de la liste
 * @return array Tableau des inscriptions (clé = nom utilisateur, valeur = tableau des noms de colonnes)
 */
function loadRegistrations($listId) {
    $registrationFile = REGISTRATIONS_DIR . 'list_' . $listId . '.json';
    
    if (!file_exists($registrationFile)) {
        // Créer le fichier s'il n'existe pas
        file_put_contents($registrationFile, '{}');
        return [];
    }
    
    $content = file_get_contents($registrationFile);
    $registrations = json_decode($content, true);
    return is_array($registrations) ? $registrations : [];
}

/**
 * Sauvegarder les inscriptions pour une liste
 * @param int $listId ID de la liste
 * @param array $registrations Tableau des inscriptions
 * @return bool Succès ou échec
 */
function saveRegistrations($listId, $registrations) {
    $registrationFile = REGISTRATIONS_DIR . 'list_' . $listId . '.json';
    $json = json_encode($registrations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($registrationFile, $json) !== false;
}

/**
 * Inscrire un utilisateur à une colonne
 * @param int $listId ID de la liste
 * @param string $userName Nom de l'utilisateur
 * @param string $column Nom de la colonne (peut être un tableau avec 'name' ou juste une chaîne)
 * @return bool Succès ou échec
 */
function registerUser($listId, $userName, $column) {
    $registrations = loadRegistrations($listId);
    
    if (!isset($registrations[$userName])) {
        $registrations[$userName] = [];
    }
    
    // Si $column est un tableau (format nouveau), on prend le name
    if (is_array($column) && isset($column['name'])) {
        $column = $column['name'];
    }
    
    // Basculer l'état (si déjà inscrit, on le retire, sinon on l'ajoute)
    $key = array_search($column, $registrations[$userName]);
    if ($key !== false) {
        unset($registrations[$userName][$key]);
        // Réindexer le tableau
        $registrations[$userName] = array_values($registrations[$userName]);
    } else {
        $registrations[$userName][] = $column;
    }
    
    // Si l'utilisateur n'a plus d'inscriptions, on le supprime
    if (empty($registrations[$userName])) {
        unset($registrations[$userName]);
    }
    
    return saveRegistrations($listId, $registrations);
}

/**
 * Vérifier si un utilisateur est inscrit à une colonne
 * @param int $listId ID de la liste
 * @param string $userName Nom de l'utilisateur
 * @param string $column Nom de la colonne
 * @return bool Inscrit ou non
 */
function isUserRegistered($listId, $userName, $column) {
    $registrations = loadRegistrations($listId);
    return isset($registrations[$userName]) && in_array($column, $registrations[$userName]);
}

/**
 * Obtenir tous les utilisateurs inscrits pour une liste
 * @param int $listId ID de la liste
 * @return array Tableau des utilisateurs (clé = nom, valeur = tableau des colonnes)
 */
function getAllRegistrations($listId) {
    return loadRegistrations($listId);
}

/**
 * Obtenir les colonnes d'une liste organisées par catégorie
 * @param array $list La liste
 * @return array Tableau organisé par catégorie
 */
function getColumnsByCategory($list) {
    $organized = [];
    
    if (empty($list['columns'])) {
        return $organized;
    }
    
    // Vérifier si les colonnes sont dans l'ancien format (tableau de chaînes)
    if (!isset($list['columns'][0]['name'])) {
        // Ancien format: convertir en nouveau format
        foreach ($list['columns'] as $colName) {
            $organized[''][] = ['name' => $colName, 'category' => ''];
        }
        return $organized;
    }
    
    // Nouveau format: déjà avec catégories
    foreach ($list['columns'] as $col) {
        $category = $col['category'] ?? '';
        if (!isset($organized[$category])) {
            $organized[$category] = [];
        }
        $organized[$category][] = $col;
    }
    
    return $organized;
}

// ============================================
// Import/Export CSV
// ============================================

/**
 * Importer une liste depuis un fichier CSV
 * @param string $filePath Chemin du fichier CSV
 * @return array|false Résultat de l'import ou false en cas d'erreur
 */
function importListFromCSV($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    if (count($lines) < 2) {
        return false;
    }
    
    // Ignorer la première ligne (en-tête)
    $dataLine = trim($lines[1]);
    if (empty($dataLine)) {
        return false;
    }
    
    $parts = str_getcsv($dataLine);
    if (count($parts) < 2) {
        return false;
    }
    
    $listName = trim($parts[0]);
    $columns = [];
    for ($i = 1; $i < count($parts); $i++) {
        $column = trim($parts[$i]);
        if (!empty($column)) {
            $columns[] = ['name' => $column, 'category' => ''];
        }
    }
    
    if (empty($listName) || empty($columns)) {
        return false;
    }
    
    // Ajouter la liste (visible et modifiable par défaut)
    $listId = addList($listName, $columns, null, null, true, false);
    
    if ($listId === false) {
        return false;
    }
    
    return [
        'id' => $listId,
        'name' => $listName,
        'columns' => $columns
    ];
}

/**
 * Exporter une liste au format CSV
 * @param int $listId ID de la liste
 * @return string|false Contenu CSV ou false en cas d'erreur
 */
function exportListToCSV($listId) {
    $list = getListById($listId);
    if (!$list) {
        return false;
    }
    
    $registrations = getAllRegistrations($listId);
    
    // En-tête
    $csv = [];
    $header = ['Utilisateur'];
    foreach ($list['columns'] as $col) {
        $header[] = is_array($col) ? $col['name'] : $col;
    }
    $csv[] = implode(',', $header);
    
    // Données
    foreach ($registrations as $userName => $userColumns) {
        $row = [$userName];
        foreach ($list['columns'] as $col) {
            $colName = is_array($col) ? $col['name'] : $col;
            $row[] = in_array($colName, $userColumns) ? 'X' : '';
        }
        $csv[] = implode(',', $row);
    }
    
    return implode("\n", $csv);
}

// ============================================
// Utilitaires
// ============================================

/**
 * Nettoyer une chaîne de caractères
 * @param string $input Chaîne à nettoyer
 * @return string Chaîne nettoyée
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Générer un nom de fichier sûr
 * @param string $name Nom à nettoyer
 * @return string Nom sûr
 */
function sanitizeFilename($name) {
    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);
    return $name;
}

/**
 * Rediriger vers une URL
 * @param string $url URL de redirection
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Générer une URL absolue
 * @param string $path Chemin relatif
 * @return string URL absolue
 */
function url($path) {
    // Si BASE_URL n'est pas défini, on le définit (fallback)
    if (!defined('BASE_URL')) {
        define('BASE_URL', '/');
    }
    // Supprimer les slashes de début de $path
    $path = ltrim($path, '/');
    // BASE_URL se termine déjà par /, donc on concatène directement
    return BASE_URL . $path;
}

/**
 * Vérifier si une liste nécessite un mot de passe
 * @param int $listId ID de la liste
 * @return bool True si un mot de passe est requis
 */
function listRequiresPassword($listId) {
    $list = getListById($listId);
    return !empty($list['password']);
}

/**
 * Vérifier le mot de passe d'une liste
 * @param int $listId ID de la liste
 * @param string $password Mot de passe à vérifier
 * @return bool True si le mot de passe est correct
 */
function checkListPassword($listId, $password) {
    $list = getListById($listId);
    if (empty($list['password'])) {
        return true; // Pas de mot de passe requis
    }
    return $list['password'] === $password;
}

/**
 * Obtenir le nom de l'utilisateur (via session ou cookie)
 * @return string Nom de l'utilisateur ou une chaîne vide
 */
function getCurrentUserName() {
    if (isset($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    }
    if (isset($_COOKIE['user_name'])) {
        return $_COOKIE['user_name'];
    }
    return '';
}

/**
 * Définir le nom de l'utilisateur (session + cookie)
 * @param string $name Nom de l'utilisateur
 */
function setCurrentUserName($name) {
    $_SESSION['user_name'] = $name;
    setcookie('user_name', $name, time() + (86400 * 30), '/'); // 30 jours
}

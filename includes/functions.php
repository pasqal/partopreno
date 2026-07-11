<?php
// ============================================
// Fonctions utilitaires pour le système d'inscription
// ============================================

require_once __DIR__ . '/config.php';

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
 * Ajouter une nouvelle liste
 * @param string $name Nom de la liste
 * @param array $columns Tableau des colonnes (noms des lignes/événements)
 * @param string|null $password Mot de passe (optionnel)
 * @return int|false ID de la nouvelle liste ou false en cas d'erreur
 */
function addList($name, $columns, $password = null) {
    $lists = loadLists();
    
    // Générer un ID unique
    $maxId = 0;
    foreach ($lists as $list) {
        if ($list['id'] > $maxId) {
            $maxId = $list['id'];
        }
    }
    $newId = $maxId + 1;
    
    $newList = [
        'id' => $newId,
        'name' => $name,
        'password' => $password,
        'columns' => $columns,
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
 * @param string|null $password Mot de passe (null pour supprimer)
 * @return bool Succès ou échec
 */
function updateList($id, $name, $columns, $password = null) {
    $lists = loadLists();
    $found = false;
    
    foreach ($lists as &$list) {
        if ($list['id'] == $id) {
            $list['name'] = $name;
            $list['columns'] = $columns;
            $list['password'] = $password;
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
 * @return array Tableau des inscriptions (clé = nom utilisateur, valeur = tableau des colonnes cochées)
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
 * @param string $column Nom de la colonne
 * @return bool Succès ou échec
 */
function registerUser($listId, $userName, $column) {
    $registrations = loadRegistrations($listId);
    
    if (!isset($registrations[$userName])) {
        $registrations[$userName] = [];
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
            $columns[] = $column;
        }
    }
    
    if (empty($listName) || empty($columns)) {
        return false;
    }
    
    // Ajouter la liste
    $listId = addList($listName, $columns);
    
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
    foreach ($list['columns'] as $column) {
        $header[] = $column;
    }
    $csv[] = implode(',', $header);
    
    // Données
    foreach ($registrations as $userName => $userColumns) {
        $row = [$userName];
        foreach ($list['columns'] as $column) {
            $row[] = in_array($column, $userColumns) ? 'X' : '';
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

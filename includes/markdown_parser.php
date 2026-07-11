<?php
// ============================================
// Parseur Markdown pour les listes
// ============================================

/**
 * Parser un fichier Markdown pour en extraire une liste
 * 
 * Format attendu :
 * # Titre de la liste
 * Description de la liste (optionnelle)
 * 
 * ## Catégorie 1
 * - Élément 1
 * - Élément 2
 * 
 * ## Catégorie 2
 * - Élément A
 * - Élément B
 * 
 * @param string $content Contenu du fichier Markdown
 * @return array Tableau avec 'name', 'description', 'columns'
 */
function parseMarkdownList($content) {
    $lines = explode("\n", $content);
    
    $result = [
        'name' => '',
        'description' => '',
        'columns' => []
    ];
    
    $currentSection = '';
    $inDescription = false;
    $descriptionLines = [];
    $hasFoundFirstSection = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Ignorer les lignes vides
        if (empty($trimmed)) {
            continue;
        }
        
        // Titre principal (#)
        if (strpos($trimmed, '#') === 0 && strpos($trimmed, '##') !== 0) {
            $result['name'] = trim(substr($trimmed, 1));
            $inDescription = true;
            continue;
        }
        
        // Sous-titre (##) - nouvelle catégorie
        if (strpos($trimmed, '##') === 0) {
            // Sauvegarder la description accumulée
            if (!empty($descriptionLines)) {
                $result['description'] = trim(implode("\n", $descriptionLines));
                $descriptionLines = [];
            }
            $currentSection = trim(substr($trimmed, 2));
            $hasFoundFirstSection = true;
            $inDescription = false;
            continue;
        }
        
        // Liste à puces
        if (strpos($trimmed, '-') === 0) {
            $item = trim(substr($trimmed, 1));
            if (!empty($item)) {
                // Si on a une catégorie active, on l'ajoute comme préfixe
                if (!empty($currentSection)) {
                    $result['columns'][] = $currentSection . ' - ' . $item;
                } else {
                    $result['columns'][] = $item;
                }
            }
            continue;
        }
        
        // Si on est en mode description (après le titre, avant le premier ##)
        if ($inDescription && !$hasFoundFirstSection) {
            $descriptionLines[] = $trimmed;
        }
    }
    
    // Sauvegarder la description finale
    if (!empty($descriptionLines)) {
        $result['description'] = trim(implode("\n", $descriptionLines));
    }
    
    return $result;
}

/**
 * Importer une liste depuis un fichier Markdown
 * 
 * @param string $filePath Chemin du fichier Markdown
 * @return array|false Résultat de l'import ou false en cas d'erreur
 */
function importListFromMarkdown($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $parsed = parseMarkdownList($content);
    
    if (empty($parsed['name']) || empty($parsed['columns'])) {
        return false;
    }
    
    // Ajouter la liste
    $listId = addList($parsed['name'], $parsed['columns'], null, $parsed['description']);
    
    if ($listId === false) {
        return false;
    }
    
    return [
        'id' => $listId,
        'name' => $parsed['name'],
        'description' => $parsed['description'],
        'columns' => $parsed['columns']
    ];
}

/**
 * Tester le parseur avec un exemple
 * Utile pour le débogage
 */
function testMarkdownParser() {
    $example1 = "# Cohésion
Liste exemple pour barbecue
lieu - date

## qui amènes :
- Boisson soft
- Boisson pas soft
- Salade

## pour la cuisson
- barbecue
- charbon
";
    
    $result = parseMarkdownList($example1);
    echo "Test Markdown Parser:\n";
    echo "Name: " . $result['name'] . "\n";
    echo "Description: " . $result['description'] . "\n";
    echo "Columns:\n";
    print_r($result['columns']);
}

// Uncomment to test
// testMarkdownParser();

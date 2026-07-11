<?php
// ============================================
// Script de migration pour ajouter les champs is_visible et is_readonly
// ============================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Charger les listes
$lists = loadLists();
$migrated = false;

foreach ($lists as &$list) {
    // Supprimer is_active si elle existe (on ne l'utilise plus)
    if (isset($list['is_active'])) {
        unset($list['is_active']);
        $migrated = true;
    }
    
    // Ajouter les champs manquants avec des valeurs par défaut
    if (!isset($list['is_visible'])) {
        $list['is_visible'] = true;
        $migrated = true;
    }
    if (!isset($list['is_readonly'])) {
        $list['is_readonly'] = false;
        $migrated = true;
    }
}

if ($migrated) {
    saveLists($lists);
    echo "Migration terminée avec succès !\n";
    echo "Les champs ont été mis à jour pour toutes les listes.\n";
} else {
    echo "Aucune migration nécessaire, les champs sont déjà à jour.\n";
}

// Afficher les listes migrées
if ($migrated) {
    echo "\nListes migrées :\n";
    foreach ($lists as $list) {
        echo "- ID {$list['id']}: {$list['name']} (visible: " . ($list['is_visible'] ? 'oui' : 'non') . 
             ", readonly: " . ($list['is_readonly'] ? 'oui' : 'non') . ")\n";
    }
}

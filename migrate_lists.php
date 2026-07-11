<?php
// ============================================
// Script de migration pour ajouter les champs is_active, is_visible, is_readonly
// ============================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Charger les listes
$lists = loadLists();
$migrated = false;

foreach ($lists as &$list) {
    // Ajouter les champs manquants avec des valeurs par défaut
    if (!isset($list['is_active'])) {
        $list['is_active'] = true;
        $migrated = true;
    }
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
    echo "Les champs is_active, is_visible et is_readonly ont été ajoutés à toutes les listes.\n";
} else {
    echo "Aucune migration nécessaire, les champs existent déjà.\n";
}

// Afficher les listes migrées
if ($migrated) {
    echo "\nListes migrées :\n";
    foreach ($lists as $list) {
        echo "- ID {$list['id']}: {$list['name']} (active: " . ($list['is_active'] ? 'oui' : 'non') . 
             ", visible: " . ($list['is_visible'] ? 'oui' : 'non') . 
             ", readonly: " . ($list['is_readonly'] ? 'oui' : 'non') . ")\n";
    }
}

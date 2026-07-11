<?php
// ============================================
// Page d'accueil - Redirection vers la partie utilisateur
// ============================================

require_once __DIR__ . '/includes/config.php';

// Rediriger vers la liste des événements
header("Location: " . BASE_URL . "user/");
exit();

<?php
// ============================================
// Page d'accueil - Redirection vers la partie utilisateur
// ============================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Rediriger vers la liste des événements
redirect(url('user/'));

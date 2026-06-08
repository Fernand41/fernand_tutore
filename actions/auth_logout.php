<?php
/**
 * actions/auth_logout.php
 * Déconnexion — détruit la session et redirige vers l'accueil
 * Peut être appelé depuis n'importe quelle page (lien direct)
 */

require_once __DIR__ . '/../includes/session.php';

clearRememberToken();
logoutUser();

// Rediriger vers login avec message de confirmation
session_start(); // Redémarrer pour pouvoir poser le flash
setFlash('success', 'Vous avez été déconnecté avec succès.');

header('Location: ../front_end/login.php');
exit;
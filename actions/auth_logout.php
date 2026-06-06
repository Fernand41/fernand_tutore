<?php
/**
 * actions/auth_logout.php
 * Déconnexion — détruit la session et redirige vers l'accueil
 * Peut être appelé depuis n'importe quelle page (lien direct)
 */

require_once __DIR__ . '/../includes/session.php';

// Supprimer le cookie "remember me" s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    // En production : invalider aussi le token en BDD
}

logoutUser();

// Rediriger vers login avec message de confirmation
session_start(); // Redémarrer pour pouvoir poser le flash
setFlash('success', 'Vous avez été déconnecté avec succès.');

header('Location: ../front_end/login.php');
exit;
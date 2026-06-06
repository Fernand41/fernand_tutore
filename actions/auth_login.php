<?php
/**
 * actions/auth_login.php
 * Traitement du formulaire de connexion (POST uniquement)
 * Appelé depuis pages/login.php
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Accepter uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/login.php');
    exit;
}

// Vérification CSRF
verifyCsrf('../front_end/login.php');

// Récupérer et nettoyer les champs
$email      = trim($_POST['email']      ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$remember   = isset($_POST['remember']);

// ── Validation basique ────────────────────────
if (empty($email) || empty($mot_de_passe)) {
    setFlash('danger', 'Veuillez remplir tous les champs.');
    header('Location: ../front_end/login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('danger', 'Adresse email invalide.');
    header('Location: ../front_end/login.php');
    exit;
}

// ── Recherche en BDD ──────────────────────────
try {
    $pdo  = Database::getInstance();
    $stmt = $pdo->prepare("SELECT id, pseudo, email, mot_de_passe, role FROM utilisateurs WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[auth_login] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue. Veuillez réessayer.');
    header('Location: ../front_end/login.php');
    exit;
}

// ── Vérification mot de passe ─────────────────
if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
    setFlash('danger', 'Email ou mot de passe incorrect.');
    header('Location: ../front_end/login.php');
    exit;
}

// ── Connexion réussie ─────────────────────────
loginUser($user);

// Cookie "se souvenir de moi" (30 jours)
if ($remember) {
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/', '', false, true);
    // En production : stocker $token hashé en BDD lié à l'utilisateur
}

setFlash('success', 'Bienvenue, ' . e($user['pseudo']) . ' !');

// Rediriger vers la page demandée avant le login, ou le profil par défaut
$redirect = $_SESSION['redirect_after_login'] ?? '../front_end/profil.php';
unset($_SESSION['redirect_after_login']);

// Sécurité : ne jamais rediriger vers une URL externe
if (!str_starts_with($redirect, '/') && !str_starts_with($redirect, '../')) {
    $redirect = '../front_end/profil.php';
}

// Si admin → tableau de bord
if ($user['role'] === 'admin') {
    header('Location: ../admin/index.php');
} else {
    header('Location: ' . $redirect);
}
exit;
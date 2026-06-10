<?php
/**
 * actions/auth_register.php
 * Traitement du formulaire d'inscription (POST uniquement)
 * Appelé depuis pages/inscription.php
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Accepter uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/inscription.php');
    exit;
}

// Vérification CSRF
verifyCsrf('../front_end/inscription.php');

// Récupérer et nettoyer les champs
$pseudo         = trim($_POST['pseudo']         ?? '');
$email          = trim($_POST['email']          ?? '');
$mot_de_passe   = $_POST['mot_de_passe']        ?? '';
$confirmation   = $_POST['confirmation']        ?? '';
$redirect       = trim($_POST['redirect']      ?? '');

// ── Validations ───────────────────────────────

$erreurs = [];

if (empty($pseudo)) {
    $erreurs[] = 'Le pseudo est obligatoire.';
} elseif (mb_strlen($pseudo) < 2 || mb_strlen($pseudo) > 50) {
    $erreurs[] = 'Le pseudo doit faire entre 2 et 50 caractères.';
}

if (empty($email)) {
    $erreurs[] = "L'adresse email est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "L'adresse email est invalide.";
}

if (empty($mot_de_passe)) {
    $erreurs[] = 'Le mot de passe est obligatoire.';
} elseif (strlen($mot_de_passe) < 8) {
    $erreurs[] = 'Le mot de passe doit faire au moins 8 caractères.';
}

if ($mot_de_passe !== $confirmation) {
    $erreurs[] = 'Les mots de passe ne correspondent pas.';
}

if (!empty($erreurs)) {
    setFlash('danger', implode('<br>', $erreurs));
    // Repasser le pseudo et email pour repré-remplir le formulaire
    $_SESSION['form_pseudo'] = $pseudo;
    $_SESSION['form_email']  = $email;
    header('Location: ../front_end/inscription.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// ── Vérifier unicité email & pseudo ──────────
try {
    $pdo = Database::getInstance();

    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('danger', 'Cette adresse email est déjà utilisée.');
        $_SESSION['form_pseudo'] = $pseudo;
        header('Location: ../front_end/inscription.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE pseudo = ? LIMIT 1");
    $stmt->execute([$pseudo]);
    if ($stmt->fetch()) {
        setFlash('danger', 'Ce pseudo est déjà pris, choisissez-en un autre.');
        $_SESSION['form_email'] = $email;
        header('Location: ../front_end/inscription.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

} catch (PDOException $e) {
    error_log('[auth_register] Erreur BDD vérif : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue. Veuillez réessayer.');
    header('Location: ../front_end/inscription.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// ── Insertion en BDD ──────────────────────────
try {
    $motDePasseHache = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (pseudo, email, mot_de_passe, role)
        VALUES (?, ?, ?, 'user')
    ");
    $stmt->execute([$pseudo, $email, $motDePasseHache]);

    $newUserId = (int) $pdo->lastInsertId();

} catch (PDOException $e) {
    error_log('[auth_register] Erreur insertion : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue lors de la création du compte.');
    header('Location: ../front_end/inscription.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// ── Connexion automatique après inscription ───
$newUser = [
    'id'    => $newUserId,
    'pseudo' => $pseudo,
    'email' => $email,
    'role'  => 'utilisateur',
];
loginUser($newUser);

setFlash('success', 'Compte créé avec succès ! Bienvenue, ' . e($pseudo) . ' 🎉');
if ($redirect !== '') {
    if (!preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $redirect)) {
        if (!preg_match('#^(?:/|\.\./)#', $redirect)) {
            $redirect = '../front_end/' . ltrim($redirect, '/');
        }
        header('Location: ' . $redirect);
        exit;
    }
}
header('Location: ../front_end/profil.php');
exit;
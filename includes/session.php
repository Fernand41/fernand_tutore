<?php
/**
 * includes/session.php
 * Démarrage de la session + fonctions d'authentification
 * À inclure EN PREMIER dans chaque page PHP.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ──────────────────────────────────────────────
//  Fonctions utilitaires de session
// ──────────────────────────────────────────────

/**
 * Vérifie si un utilisateur est connecté.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur.
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Retourne l'ID de l'utilisateur connecté, ou null.
 */
function currentUserId(): ?int {
    return isLoggedIn() ? (int) $_SESSION['user_id'] : null;
}

/**
 * Retourne le pseudo de l'utilisateur connecté, ou null.
 */
function currentUserName(): ?string {
    return isLoggedIn() ? ($_SESSION['user_pseudo'] ?? null) : null;
}

/**
 * Retourne le rôle de l'utilisateur connecté, ou null.
 */
function currentUserRole(): ?string {
    return isLoggedIn() ? ($_SESSION['user_role'] ?? null) : null;
}

/**
 * Protège une page : redirige vers login si non connecté.
 * Usage : requireAuth() en haut des pages protégées.
 */
function requireAuth(string $redirectTo = '../pages/login.php'): void {
    if (!isLoggedIn()) {
        // Sauvegarder l'URL demandée pour rediriger après login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Protège une page admin : redirige si non admin.
 * Usage : requireAdmin() en haut de chaque page admin/.
 */
function requireAdmin(string $redirectTo = '../pages/login.php'): void {
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Connecte un utilisateur en remplissant la session.
 * À appeler après vérification du mot de passe.
 *
 * @param array $user  Ligne de la table utilisateurs (id, pseudo, email, role)
 */
function loginUser(array $user): void {
    session_regenerate_id(true); // Prévenir le session fixation
    $_SESSION['user_id']     = (int)    $user['id'];
    $_SESSION['user_pseudo'] = (string) ($user['pseudo'] ?? $user['nom'] ?? 'Utilisateur');
    $_SESSION['user_email']  = (string) $user['email'];
    $_SESSION['user_role']   = (string) $user['role'];
}

/**
 * Déconnecte l'utilisateur et détruit la session.
 */
function logoutUser(): void {
    clearRememberToken();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}

/**
 * Retourne le jeton de rappel depuis le cookie, s'il existe.
 */
function getRememberToken(): ?string {
    return $_COOKIE['remember_token'] ?? null;
}

/**
 * Charge l'utilisateur depuis le token de rappel en base de données.
 */
function loadRememberedUser(): void {
    if (isLoggedIn()) {
        return;
    }

    $token = getRememberToken();
    if (empty($token)) {
        return;
    }

    require_once __DIR__ . '/../config/database.php';

    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.pseudo, u.email, u.role
             FROM remember_tokens rt
             JOIN utilisateurs u ON u.id = rt.id_user
             WHERE rt.token_hash = ? AND rt.date_expiration > NOW()
             LIMIT 1'
        );
        $stmt->execute([hash('sha256', $token)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            loginUser($user);
        }
    } catch (PDOException $e) {
        error_log('[session] Impossible de charger remember_token : ' . $e->getMessage());
    }
}

/**
 * Enregistre un token de rappel en base de données et en cookie.
 */
function rememberUser(int $userId): void {
    require_once __DIR__ . '/../config/database.php';

    try {
        $pdo = Database::getInstance();
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $expires = new DateTime('+30 days');

        $pdo->prepare('DELETE FROM remember_tokens WHERE id_user = ?')->execute([$userId]);
        $stmt = $pdo->prepare(
            'INSERT INTO remember_tokens (id_user, token_hash, ip, user_agent, date_expiration)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $hash,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            $expires->format('Y-m-d H:i:s'),
        ]);

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('remember_token', $token, [
            'expires' => $expires->getTimestamp(),
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } catch (PDOException $e) {
        error_log('[session] Impossible de créer remember_token : ' . $e->getMessage());
    }
}

/**
 * Supprime le cookie et le token de rappel de la base.
 */
function clearRememberToken(): void {
    $token = getRememberToken();
    if (!empty($token)) {
        require_once __DIR__ . '/../config/database.php';
        try {
            $pdo = Database::getInstance();
            $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?')->execute([hash('sha256', $token)]);
        } catch (PDOException $e) {
            error_log('[session] Impossible de supprimer remember_token : ' . $e->getMessage());
        }
    }

    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Charger l'utilisateur depuis le cookie "se souvenir de moi".
if (!isLoggedIn()) {
    loadRememberedUser();
}

// ──────────────────────────────────────────────
//  Flash messages (affichage unique après redirect)
// ──────────────────────────────────────────────

/**
 * Enregistre un message flash (success | danger | warning | info).
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Récupère et efface le message flash.
 * Retourne null s'il n'y en a pas.
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Affiche le message flash sous forme d'alerte Bootstrap.
 * À appeler une fois dans le template, après le header.
 */
function displayFlash(): void {
    $flash = getFlash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $msg  = htmlspecialchars($flash['message']);
        echo <<<HTML
        <div class="alert alert-{$type} alert-dismissible fade show mx-3 mt-3" role="alert">
            {$msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        HTML;
    }
}

// ──────────────────────────────────────────────
//  Helpers sécurité / affichage
// ──────────────────────────────────────────────

/**
 * Échappe une valeur pour l'affichage HTML (shorthand).
 */
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

/**
 * Génère un token CSRF et le stocke en session.
 * À placer dans les formulaires : <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF soumis via POST.
 * Redirige avec message d'erreur si invalide.
 */
function verifyCsrf(string $redirectTo = '/'): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        setFlash('danger', 'Requête invalide. Veuillez réessayer.');
        header('Location: ' . $redirectTo);
        exit;
    }
}
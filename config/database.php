<?php
/**
 * Configuration de la base de données et constantes globales
 */

// ─── Connexion BDD ────────────────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_NAME',    getenv('DB_NAME')    ?: 'gouts_benin');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ─── Sécurité ─────────────────────────────────────────────────────────────────

$jwtSecret = getenv('JWT_SECRET');
if (empty($jwtSecret)) {
    
    $jwtSecret = 'K7#nP2$xR9!vQ4&mL6@wY3%cF8*jH5^bD1_eS0TgA';
}
define('JWT_SECRET', $jwtSecret);

// ─── Chemins ──────────────────────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('BASE_URL',   getenv('BASE_URL') ?: 'http://localhost/gouts_benin');

// ─── Singleton PDO ────────────────────────────────────────────────────────────
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public function __wakeup(): void {
        throw new Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn     = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Logger l'erreur réelle côté serveur, ne jamais l'exposer au client
                error_log('[Database] Connexion échouée : ' . $e->getMessage());
                header('Content-Type: application/json', true, 500);
                echo json_encode([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Erreur de connexion à la base de données.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        return self::$instance;
    }
}
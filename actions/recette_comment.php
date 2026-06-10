<?php
/**
 * actions/recette_comment.php
 * Traitement du formulaire de commentaire pour une recette.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Debug: enregistrer état POST et session pour investigation
@file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "--- " . date('c') . " ---\n" . print_r([
    'POST' => $_POST,
    'SESSION_user_id' => $_SESSION['user_id'] ?? null,
    'SESSION_csrf' => $_SESSION['csrf_token'] ?? null,
], true), FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/recettes.php');
    exit;
}

$idRecette = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : 0;
if (!isLoggedIn() && $idRecette > 0) {
    $_SESSION['redirect_after_login'] = '../front_end/recette.php?id=' . $idRecette;
}

requireAuth('../front_end/login.php');
verifyCsrf('../front_end/recettes.php');

$idRecette = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : 0;
$note = isset($_POST['note']) ? (int) $_POST['note'] : 0;
$contenu = trim($_POST['contenu'] ?? '');

if ($idRecette <= 0 || $note < 1 || $note > 5 || $contenu === '') {
    setFlash('danger', 'Veuillez renseigner une note valide et un commentaire.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
}

try {
    $pdo = Database::getInstance();

    // Log des colonnes réelles de la table commentaires (diagnostic)
    try {
        $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commentaires'");
        $colStmt->execute();
        $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
        @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "commentaires columns: " . json_encode($cols) . "\n", FILE_APPEND);
    } catch (Exception $ee) {
        @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "Erreur lecture colonnes: " . $ee->getMessage() . "\n", FILE_APPEND);
    }

    $stmt = $pdo->prepare('SELECT id FROM recettes WHERE id = ? AND statut = "publie" LIMIT 1');
    $stmt->execute([$idRecette]);
    $found = $stmt->fetch();
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "check recette fetch: " . var_export((bool)$found, true) . "\n", FILE_APPEND);
    if (!$found) {
        setFlash('danger', 'Recette introuvable ou non publiée.');
        header('Location: ../front_end/recettes.php');
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO commentaires (id_recette, id_utilisateur, contenu, statut) VALUES (?, ?, ?, "en_attente")');
    $res1 = $stmt->execute([$idRecette, currentUserId(), $contenu]);
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "insert commentaires result: " . var_export($res1, true) . " | err: " . json_encode($stmt->errorInfo()) . "\n", FILE_APPEND);

    $noteStmt = $pdo->prepare(
        'INSERT INTO notes (id_recette, id_utilisateur, valeur)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)'
    );
    $res2 = $noteStmt->execute([$idRecette, currentUserId(), $note]);
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "upsert note result: " . var_export($res2, true) . " | err: " . json_encode($noteStmt->errorInfo()) . "\n", FILE_APPEND);

    // Mettre à jour la note moyenne sur la recette.
    $avgStmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(valeur), 1), 0) AS average FROM notes WHERE id_recette = ?');
    $avgStmt->execute([$idRecette]);
    $stats = $avgStmt->fetch();
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "avg stats: " . json_encode($stats) . "\n", FILE_APPEND);
    $updateStmt = $pdo->prepare('UPDATE recettes SET note_moyenne = ?, nb_notes = ? WHERE id = ?');
    $res3 = $updateStmt->execute([$stats['average'], $stats['total'], $idRecette]);
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "update recette result: " . var_export($res3, true) . " | err: " . json_encode($updateStmt->errorInfo()) . "\n", FILE_APPEND);

    $pdo->commit();
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "commit ok\n", FILE_APPEND);

    setFlash('success', 'Merci pour votre avis ! Votre commentaire sera publié après validation.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[recette_comment] Erreur BDD : ' . $e->getMessage());
    @file_put_contents(__DIR__ . '/../tests/debug_recette_comment.log', "PDOException: " . $e->getMessage() . "\n", FILE_APPEND);
    setFlash('danger', 'Une erreur est survenue lors de l’envoi de votre avis.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
}

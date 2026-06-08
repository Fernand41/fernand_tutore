<?php
/**
 * actions/recette_comment.php
 * Traitement du formulaire de commentaire pour une recette.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/recettes.php');
    exit;
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

    $stmt = $pdo->prepare('SELECT id FROM recettes WHERE id = ? AND statut = "publie" LIMIT 1');
    $stmt->execute([$idRecette]);
    if (!$stmt->fetch()) {
        setFlash('danger', 'Recette introuvable ou non publiée.');
        header('Location: ../front_end/recettes.php');
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO commentaires (id_recette, id_user, contenu, statut) VALUES (?, ?, ?, "en_attente")');
    $stmt->execute([$idRecette, currentUserId(), $contenu]);

    $noteStmt = $pdo->prepare(
        'INSERT INTO notes (id_recette, id_user, valeur)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)'
    );
    $noteStmt->execute([$idRecette, currentUserId(), $note]);

    // Mettre à jour la note moyenne sur la recette.
    $avgStmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(ROUND(AVG(valeur), 1), 0) AS average FROM notes WHERE id_recette = ?');
    $avgStmt->execute([$idRecette]);
    $stats = $avgStmt->fetch();
    $updateStmt = $pdo->prepare('UPDATE recettes SET note_moyenne = ?, nb_notes = ? WHERE id = ?');
    $updateStmt->execute([$stats['average'], $stats['total'], $idRecette]);

    $pdo->commit();

    setFlash('success', 'Merci pour votre avis ! Votre commentaire sera publié après validation.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[recette_comment] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue lors de l’envoi de votre avis.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
}

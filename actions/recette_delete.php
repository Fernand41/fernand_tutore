<?php
/**
 * actions/recette_delete.php
 * Supprime une recette appartenant à l'utilisateur connecté.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/profil.php');
    exit;
}

requireAuth('../front_end/login.php');
verifyCsrf('../front_end/profil.php');

$idRecette = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : 0;
$userId = currentUserId();

if ($idRecette <= 0) {
    setFlash('danger', 'Recette invalide.');
    header('Location: ../front_end/profil.php');
    exit;
}

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT id, image FROM recettes WHERE id = ? AND id_auteur = ? LIMIT 1');
    $stmt->execute([$idRecette, $userId]);
    $recette = $stmt->fetch();
    if (!$recette) {
        setFlash('danger', 'Recette introuvable ou non autorisée.');
        header('Location: ../front_end/profil.php');
        exit;
    }

    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM recettes WHERE id = ?')->execute([$idRecette]);
    $pdo->prepare('DELETE FROM favoris WHERE id_recette = ?')->execute([$idRecette]);
    $pdo->prepare('DELETE FROM commentaires WHERE id_recette = ?')->execute([$idRecette]);
    $pdo->prepare('DELETE FROM notes WHERE id_recette = ?')->execute([$idRecette]);
    $pdo->commit();

    if (!empty($recette['image'])) {
        $imagePath = __DIR__ . '/../uploads/recettes/' . $recette['image'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    setFlash('success', 'Recette supprimée avec succès.');
    header('Location: ../front_end/profil.php');
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[recette_delete] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Impossible de supprimer la recette.');
    header('Location: ../front_end/profil.php');
    exit;
}

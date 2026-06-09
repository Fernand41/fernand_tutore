<?php
/**
 * actions/recette_favorite.php
 * Ajoute ou supprime une recette des favoris de l'utilisateur.
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
$userId = currentUserId();

if ($idRecette <= 0) {
    setFlash('danger', 'Recette invalide.');
    header('Location: ../front_end/recettes.php');
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

    $check = $pdo->prepare('SELECT id FROM favoris WHERE id_utilisateur = ? AND id_recette = ? LIMIT 1');
    $check->execute([$userId, $idRecette]);
    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM favoris WHERE id_utilisateur = ? AND id_recette = ?')->execute([$userId, $idRecette]);
        setFlash('success', 'Recette retirée de vos favoris.');
    } else {
        $pdo->prepare('INSERT INTO favoris (id_utilisateur, id_recette) VALUES (?, ?)')->execute([$userId, $idRecette]);
        setFlash('success', 'Recette ajoutée à vos favoris.');
    }

    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
} catch (PDOException $e) {
    error_log('[recette_favorite] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue.');
    header('Location: ../front_end/recette.php?id=' . $idRecette);
    exit;
}

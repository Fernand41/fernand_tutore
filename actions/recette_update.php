<?php
/**
 * actions/recette_update.php
 * Mise à jour d'une recette existante par son auteur.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_end/profil.php');
    exit;
}

requireAuth('../front_end/login.php');

$idRecette    = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : 0;
$returnTo     = trim($_POST['return_to'] ?? '');
if ($returnTo === '' || strpos($returnTo, '../front_end/') !== 0) {
    $returnTo = '../front_end/modifier-recette.php?id=' . $idRecette;
}
@file_put_contents(__DIR__ . '/../tests/debug_recette_update.log', "--- " . date('c') . " ---\n" . print_r([
    'POST' => $_POST,
    'FILES' => isset($_FILES) ? array_map(fn($f) => ['name' => $f['name'], 'error' => $f['error'] ?? null, 'size' => $f['size'] ?? null], $_FILES) : [],
    'SESSION_user_id' => $_SESSION['user_id'] ?? null,
    'SESSION_csrf' => $_SESSION['csrf_token'] ?? null,
    'return_to' => $returnTo,
], true), FILE_APPEND);
verifyCsrf($returnTo);
$titre         = trim($_POST['titre'] ?? '');
$description   = trim($_POST['description'] ?? '');
$ingredients   = trim($_POST['ingredients'] ?? '');
$etapes        = trim($_POST['etapes'] ?? '');
$video_youtube = trim($_POST['video_youtube'] ?? '');
$difficulte    = trim($_POST['difficulte'] ?? '');
$temps_prep    = (int) ($_POST['temps_prep'] ?? 0);
$temps_cuisson = (int) ($_POST['temps_cuisson'] ?? 0);
$nb_personnes  = (int) ($_POST['portion'] ?? 0);
$id_categorie  = (int) ($_POST['id_categorie'] ?? 0);

if ($idRecette <= 0) {
    setFlash('danger', 'Recette invalide.');
    header('Location: ../front_end/profil.php');
    exit;
}

$erreurs = [];
if (empty($titre) || mb_strlen($titre) < 3) {
    $erreurs[] = 'Le titre est obligatoire (3 caractères minimum).';
}
if (empty($description)) {
    $erreurs[] = 'La description est obligatoire.';
}
if (empty($ingredients)) {
    $erreurs[] = 'La liste des ingrédients est obligatoire.';
}
if (empty($etapes)) {
    $erreurs[] = 'Les étapes de préparation sont obligatoires.';
}
if (!empty($video_youtube) && !filter_var($video_youtube, FILTER_VALIDATE_URL)) {
    $erreurs[] = 'L’URL de la vidéo est invalide.';
}
if (empty($difficulte) || !in_array($difficulte, ['facile', 'moyen', 'difficile'])) {
    $erreurs[] = 'Veuillez choisir un niveau de difficulté.';
}
if ($id_categorie <= 0) {
    $erreurs[] = 'Veuillez choisir une catégorie.';
}
if ($temps_prep <= 0) {
    $erreurs[] = 'Le temps de préparation doit être supérieur à 0.';
}

if (!empty($erreurs)) {
    setFlash('danger', implode('<br>', $erreurs));
    header('Location: ' . $returnTo);
    exit;
}

$imageFilename = null;
try {
    $pdo = Database::getInstance();

    $stmt = $pdo->prepare('SELECT id, image FROM recettes WHERE id = ? AND id_auteur = ? LIMIT 1');
    $stmt->execute([$idRecette, currentUserId()]);
    $existing = $stmt->fetch();
    if (!$existing) {
        setFlash('danger', 'Recette introuvable ou non autorisée.');
        header('Location: ../front_end/profil.php');
        exit;
    }

    $catStmt = $pdo->prepare('SELECT id FROM categories_recettes WHERE id = ?');
    $catStmt->execute([$id_categorie]);
    if (!$catStmt->fetch()) {
        setFlash('danger', 'Catégorie invalide.');
        header('Location: ' . $returnTo);
        exit;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file      = $_FILES['image'];
        $maxSize   = 3 * 1024 * 1024;
        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $uploadDir = __DIR__ . '/../uploads/recettes/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', "Erreur lors de l'upload de l'image (code : {$file['error']}).");
            header('Location: ' . $returnTo);
            exit;
        }

        if ($file['size'] > $maxSize) {
            setFlash('danger', "L'image ne doit pas dépasser 3 Mo.");
            header('Location: ' . $returnTo);
            exit;
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowed)) {
            setFlash('danger', "Format d'image non supporté. Utilisez JPG, PNG ou WebP.");
            header('Location: ' . $returnTo);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $imageFilename = 'recette_' . uniqid() . '.' . strtolower($ext);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageFilename)) {
            setFlash('danger', "Impossible de sauvegarder l'image. Vérifiez les permissions.");
            header('Location: ' . $returnTo);
            exit;
        }
    }

    $sql = 'UPDATE recettes SET titre = ?, description = ?, ingredients = ?, etapes = ?, video_youtube = ?, difficulte = ?, temps_prep = ?, temps_cuisson = ?, nb_personnes = ?, id_categorie = ?';
    if ($imageFilename !== null) {
        $sql .= ', image = ?';
    }
    $sql .= ' WHERE id = ? AND id_auteur = ?';

    $params = [$titre, $description, $ingredients, $etapes, $video_youtube, $difficulte, $temps_prep, $temps_cuisson, $nb_personnes, $id_categorie];
    if ($imageFilename !== null) {
        $params[] = $imageFilename;
    }
    $params[] = $idRecette;
    $params[] = currentUserId();

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($imageFilename !== null && !empty($existing['image'])) {
        $oldImage = __DIR__ . '/../uploads/recettes/' . $existing['image'];
        if (file_exists($oldImage)) {
            @unlink($oldImage);
        }
    }

    setFlash('success', 'Recette mise à jour avec succès.');
    header('Location: ../front_end/profil.php');
    exit;
} catch (PDOException $e) {
    if ($imageFilename !== null && file_exists(__DIR__ . '/../uploads/recettes/' . $imageFilename)) {
        @unlink(__DIR__ . '/../uploads/recettes/' . $imageFilename);
    }
    $logMessage = '[recette_update] Erreur BDD : ' . $e->getMessage() . "\n";
    $logMessage .= "Request POST: " . print_r($_POST, true) . "\n";
    $logMessage .= "Request FILES: " . print_r($_FILES, true) . "\n";
    @file_put_contents(__DIR__ . '/../tests/debug_recette_update.log', $logMessage, FILE_APPEND);
    error_log($logMessage);
    setFlash('danger', 'Une erreur est survenue lors de la mise à jour. Veuillez vérifier les champs et réessayer.');
    header('Location: ' . $returnTo);
    exit;
}

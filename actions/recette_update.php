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
verifyCsrf('../front_end/modifier-recette.php');

$idRecette    = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : 0;
$titre        = trim($_POST['titre'] ?? '');
$description  = trim($_POST['description'] ?? '');
$ingredients  = trim($_POST['ingredients'] ?? '');
$etapes       = trim($_POST['etapes'] ?? '');
$video_url    = trim($_POST['video_url'] ?? '');
$difficulte    = trim($_POST['difficulte'] ?? '');
$temps_prep   = (int) ($_POST['temps_prep'] ?? 0);
$temps_cuisson= (int) ($_POST['temps_cuisson'] ?? 0);
$nb_personnes = (int) ($_POST['portion'] ?? 0);
$id_categorie = (int) ($_POST['id_categorie'] ?? 0);

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
if (!empty($video_url) && !filter_var($video_url, FILTER_VALIDATE_URL)) {
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
    header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
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
        header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
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
            header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
            exit;
        }

        if ($file['size'] > $maxSize) {
            setFlash('danger', "L'image ne doit pas dépasser 3 Mo.");
            header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
            exit;
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowed)) {
            setFlash('danger', "Format d'image non supporté. Utilisez JPG, PNG ou WebP.");
            header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $imageFilename = 'recette_' . uniqid() . '.' . strtolower($ext);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageFilename)) {
            setFlash('danger', "Impossible de sauvegarder l'image. Vérifiez les permissions.");
            header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
            exit;
        }
    }

    $sql = 'UPDATE recettes SET titre = ?, description = ?, ingredients = ?, etapes = ?, video_url = ?, difficulte = ?, temps_prep = ?, temps_cuisson = ?, nb_personnes = ?, id_categorie = ?';
    if ($imageFilename !== null) {
        $sql .= ', image = ?';
    }
    $sql .= ' WHERE id = ? AND id_auteur = ?';

    $params = [$titre, $description, $ingredients, $etapes, $video_url, $difficulte, $temps_prep, $temps_cuisson, $nb_personnes, $id_categorie];
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
    error_log('[recette_update] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue lors de la mise à jour.');
    header('Location: ../front_end/modifier-recette.php?id=' . $idRecette);
    exit;
}

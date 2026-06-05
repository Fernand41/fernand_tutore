<?php
/**
 * actions/recette_submit.php
 * Traitement du formulaire de soumission de recette (POST uniquement)
 * Appelé depuis pages/soumettre.php
 * Utilisateur connecté obligatoire.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/soumettre.php');
    exit;
}

// Utilisateur connecté obligatoire
requireAuth('../pages/login.php');

// Vérification CSRF
verifyCsrf('../pages/soumettre.php');

// ── Récupérer les champs ──────────────────────
$titre         = trim($_POST['titre']         ?? '');
$description   = trim($_POST['description']   ?? '');
$ingredients   = trim($_POST['ingredients']   ?? '');
$etapes        = trim($_POST['etapes']        ?? '');
$difficulte    = trim($_POST['difficulte']    ?? '');
$temps_prep    = (int) ($_POST['temps_prep']    ?? 0);
$temps_cuisson = (int) ($_POST['temps_cuisson'] ?? 0);
$portion       = (int) ($_POST['portion']       ?? 0);
$id_categorie  = (int) ($_POST['id_categorie']  ?? 0);

// ── Validations ───────────────────────────────
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
    header('Location: ../pages/soumettre.php');
    exit;
}

// ── Connexion BDD ─────────────────────────────
try {
    $pdo = Database::getInstance();

    // Vérifier que la catégorie existe
    $catStmt = $pdo->prepare("SELECT id FROM categories_recettes WHERE id = ?");
    $catStmt->execute([$id_categorie]);
    if (!$catStmt->fetch()) {
        setFlash('danger', 'La catégorie choisie est invalide.');
        header('Location: ../pages/soumettre.php');
        exit;
    }

} catch (PDOException $e) {
    error_log('[recette_submit] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue. Veuillez réessayer.');
    header('Location: ../pages/soumettre.php');
    exit;
}

// ── Gestion de l'image (optionnelle) ─────────
$imageFilename = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file      = $_FILES['image'];
    $maxSize   = 3 * 1024 * 1024; // 3 Mo
    $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
    $uploadDir = __DIR__ . '/../uploads/recettes/';

    // Créer le dossier si absent
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlash('danger', "Erreur lors de l'upload de l'image (code : {$file['error']}).");
        header('Location: ../pages/soumettre.php');
        exit;
    }

    if ($file['size'] > $maxSize) {
        setFlash('danger', "L'image ne doit pas dépasser 3 Mo.");
        header('Location: ../pages/soumettre.php');
        exit;
    }

    // Vérifier le type MIME réel (pas seulement l'extension)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowed)) {
        setFlash('danger', "Format d'image non supporté. Utilisez JPG, PNG ou WebP.");
        header('Location: ../pages/soumettre.php');
        exit;
    }

    $ext           = pathinfo($file['name'], PATHINFO_EXTENSION);
    $imageFilename = 'recette_' . uniqid() . '.' . strtolower($ext);

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageFilename)) {
        setFlash('danger', "Impossible de sauvegarder l'image. Vérifiez les permissions.");
        header('Location: ../pages/soumettre.php');
        exit;
    }
}

// ── Générer un slug unique ────────────────────
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
    ]);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-');
}

$slug     = slugify($titre);
$baseSlug = $slug;
$i        = 1;

while (true) {
    $checkStmt = $pdo->prepare("SELECT id FROM recettes WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if (!$checkStmt->fetch()) break;
    $slug = $baseSlug . '-' . $i++;
}

// ── Insertion en BDD ──────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO recettes
            (titre, slug, description, ingredients, etapes, difficulte,
             temps_prep, temps_cuisson, portion, image, id_categorie, id_auteur, statut, created_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
    ");

    $stmt->execute([
        $titre, $slug, $description, $ingredients, $etapes, $difficulte,
        $temps_prep, $temps_cuisson, $portion,
        $imageFilename,
        $id_categorie,
        currentUserId(),
    ]);

} catch (PDOException $e) {
    // Supprimer l'image uploadée si l'insertion échoue
    if ($imageFilename && file_exists(__DIR__ . '/../uploads/recettes/' . $imageFilename)) {
        @unlink(__DIR__ . '/../uploads/recettes/' . $imageFilename);
    }
    error_log('[recette_submit] Erreur insertion : ' . $e->getMessage());
    setFlash('danger', 'Une erreur est survenue lors de la soumission. Veuillez réessayer.');
    header('Location: ../pages/soumettre.php');
    exit;
}

// ── Succès ────────────────────────────────────
setFlash('success', '✅ Votre recette <strong>' . e($titre) . '</strong> a été soumise avec succès ! Elle sera publiée après validation par l\'équipe.');
header('Location: ../pages/profil.php');
exit;
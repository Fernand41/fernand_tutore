<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getInstance();

requireAuth('login.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: profil.php');
    exit;
}

$recipe = null;
$categories = [];

try {
    $stmt = $pdo->prepare('SELECT * FROM recettes WHERE id = ? AND id_auteur = ? LIMIT 1');
    $stmt->execute([$id, currentUserId()]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        setFlash('danger', 'Recette introuvable ou non autorisée.');
        header('Location: profil.php');
        exit;
    }

    $catStmt = $pdo->query('SELECT id, nom FROM categories_recettes ORDER BY nom ASC');
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) {
    error_log('[modifier_recette] Erreur BDD : ' . $e->getMessage());
    setFlash('danger', 'Impossible de charger la recette.');
    header('Location: profil.php');
    exit;
}

function parseList(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$currentImage = $recipe['image'] ? 'uploads/recettes/' . $recipe['image'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="GoûtsBénin">
      <meta name="description" content="Modifier une recette soumise sur Goûts du Bénin.">
      <title>Modifier une recette - GoûtsBénin</title>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet"/>
      <link href="css/bootstrap.min.css" rel="stylesheet"/>
      <link href="css/aos.css" rel="stylesheet"/>
      <link href="css/swiper-bundle.min.css" rel="stylesheet"/>
      <link rel="stylesheet" href="css/all.min.css"/>
      <link rel="stylesheet" href="css/magnific-popup.css"/>
      <link rel="stylesheet" href="css/style.css" />
      <style>
         .form-card { background: #fff; border-radius: 16px; padding: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); }
         .img-preview-box { cursor: pointer; border: 2px dashed rgba(0,0,0,0.08); border-radius: 16px; padding: 35px; text-align: center; }
         .img-preview-box img { max-width: 100%; border-radius: 12px; margin-top: 20px; }
      </style>
   </head>
   <body>
      <section class="py-5" style="background: var(--bg-color);">
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-lg-10" data-aos="fade-up">
                  <div class="form-card">
                     <h3 class="fw-bold mb-4" style="color: var(--dark);"><i class="fas fa-edit text-danger me-2"></i>Modifier la recette</h3>
                     <?php displayFlash(); ?>
                     <form id="editRecipeForm" action="../actions/recette_update.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id_recette" value="<?= (int) $recipe['id'] ?>">
                        <div class="row g-4">
                           <div class="col-md-8">
                              <div class="form-group">
                                 <label for="recipeTitleInput" class="form-label-custom">Titre de la recette</label>
                                 <input type="text" class="form-control form-control-custom" id="recipeTitleInput" name="titre" value="<?= e($recipe['titre']) ?>" required>
                              </div>
                           </div>

                           <div class="col-md-4">
                              <div class="form-group">
                                 <label for="recipeCategorySelect" class="form-label-custom">Catégorie</label>
                                 <select class="form-select form-control-custom" id="recipeCategorySelect" name="id_categorie" required>
                                    <option value="" disabled>Choisir une catégorie</option>
                                    <?php foreach ($categories as $cat): ?>
                                       <option value="<?= e($cat['id']) ?>" <?= (int) $recipe['id_categorie'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                 </select>
                              </div>
                           </div>

                           <div class="col-12">
                              <div class="form-group">
                                 <label for="recipeDescInput" class="form-label-custom">Courte description</label>
                                 <textarea class="form-control form-control-custom" id="recipeDescInput" name="description" rows="3" required><?= e($recipe['description']) ?></textarea>
                              </div>
                           </div>

                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipeDifficultySelect" class="form-label-custom">Difficulté</label>
                                 <select class="form-select form-control-custom" id="recipeDifficultySelect" name="difficulte" required>
                                    <option value="facile" <?= $recipe['difficulte'] === 'facile' ? 'selected' : '' ?>>Facile</option>
                                    <option value="moyen" <?= $recipe['difficulte'] === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                                    <option value="difficile" <?= $recipe['difficulte'] === 'difficile' ? 'selected' : '' ?>>Difficile</option>
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipePrepInput" class="form-label-custom">Temps prép (min)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipePrepInput" name="temps_prep" min="1" value="<?= (int) $recipe['temps_prep'] ?>" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipeCookInput" class="form-label-custom">Temps cuisson (min)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipeCookInput" name="temps_cuisson" min="0" value="<?= (int) $recipe['temps_cuisson'] ?>" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipePortionsInput" class="form-label-custom">Portions (pers.)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipePortionsInput" name="portion" min="1" value="<?= (int) $recipe['nb_personnes'] ?>" required>
                              </div>
                           </div>

                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="recipeIngredientsInput" class="form-label-custom">Ingrédients (un par ligne)</label>
                                 <textarea class="form-control form-control-custom" id="recipeIngredientsInput" name="ingredients" rows="8" required><?= e($recipe['ingredients']) ?></textarea>
                              </div>
                           </div>

                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="recipeStepsInput" class="form-label-custom">Étapes de préparation (une par ligne)</label>
                                 <textarea class="form-control form-control-custom" id="recipeStepsInput" name="etapes" rows="8" required><?= e($recipe['etapes']) ?></textarea>
                              </div>
                           </div>

                           <div class="col-12">
                              <div class="form-group">
                                 <label for="recipeVideoInput" class="form-label-custom">URL vidéo YouTube</label>
                                 <input type="url" class="form-control form-control-custom" id="recipeVideoInput" name="video_url" value="<?= e($recipe['video_url'] ?? '') ?>">
                                 <small class="text-muted">Optionnel : lien vers une vidéo de préparation.</small>
                              </div>
                           </div>

                           <div class="col-12">
                              <div class="form-group">
                                 <label class="form-label-custom">Image de présentation</label>
                                 <div class="img-preview-box" id="imageBox">
                                    <i class="far fa-image"></i>
                                    <span class="text-secondary small fw-semibold">Cliquez pour choisir ou glisser-déposer une image</span>
                                    <span class="text-muted small mt-1">Formats acceptés : JPG, PNG, WEBP (Max 5Mo)</span>
                                    <?php if ($currentImage): ?>
                                       <img src="<?= e($currentImage) ?>" id="imagePreview" alt="Prévisualisation" />
                                    <?php else: ?>
                                       <img src="#" id="imagePreview" alt="Prévisualisation" style="display:none;" />
                                    <?php endif; ?>
                                 </div>
                                 <input type="file" id="recipeImageFile" name="image" accept="image/*" class="d-none">
                                 <div class="form-text">Laissez vide pour conserver l'image actuelle.</div>
                              </div>
                           </div>

                           <div class="col-12 d-flex justify-content-end gap-3 mt-4 border-top pt-4">
                              <a href="profil.php" class="btn btn-outline-dark px-4 py-2">Annuler</a>
                              <button type="submit" class="btn btn-danger px-5 py-2 fw-semibold"><i class="fas fa-save me-1"></i> Enregistrer</button>
                           </div>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>
   </body>
</html>

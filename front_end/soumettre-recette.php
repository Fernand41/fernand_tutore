<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
requireAuth('login.php');

$pdo = Database::getInstance();
$categories = $pdo->query("SELECT id, nom FROM categories_recettes ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="GoûtsBénin">
      <meta name="description" content="Partagez votre savoir-faire culinaire. Soumettez une recette béninoise sur Goûts du Bénin.">
      <title>Proposer une Recette - GoûtsBénin</title>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet"/>
      <!-- Bootstrap 5.3 -->
      <link href="css/bootstrap.min.css" rel="stylesheet"/>
      <!-- AOS Animate on Scroll -->
      <link href="css/aos.css" rel="stylesheet"/>
      <!-- Swiper -->
      <link href="css/swiper-bundle.min.css" rel="stylesheet"/>
      <!-- all min css -->
      <link rel="stylesheet" href="css/all.min.css"/>
      <!-- magnific CSS -->
      <link rel="stylesheet" href="css/magnific-popup.css"/>
      <!-- Style CSS -->
      <link rel="stylesheet" href="css/style.css" />
      <style>
         .submit-header {
            position: relative;
            padding: 120px 0 60px 0;
            background: linear-gradient(135deg, var(--dark), #151515);
            color: #fff;
         }
         .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
            margin-top: -40px;
            position: relative;
            z-index: 10;
         }
         .form-label-custom {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
         }
         .form-control-custom {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #ddd;
            font-size: 0.95rem;
            transition: all 0.2s ease;
         }
         .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230, 92, 92, 0.15);
         }
         .img-preview-box {
            width: 100%;
            height: 220px;
            border: 2px dashed #ccc;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            overflow: hidden;
            background: #fdfdfd;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
         }
         .img-preview-box:hover {
            border-color: var(--primary);
            background: #fff5f5;
         }
         .img-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
            display: none;
         }
         .img-preview-box i {
            font-size: 2.5rem;
            color: #aaa;
            margin-bottom: 10px;
         }
      </style>
   </head>
   <body>
      <?php displayFlash(); ?>
      <!-- ============================================================
         TOP BAR
         ============================================================ -->
      <div id="topbar">
         <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
               <div class="top-contact d-flex flex-wrap">
                  <span><i class="fas fa-phone-alt"></i>+229 (01) 54006206</span>
                  <span><i class="fas fa-envelope"></i>contact@goutbenin.bj</span>
                  <span><i class="fas fa-map-marker-alt"></i>Cotonou, Bénin</span>
               </div>
               <div class="d-flex align-items-center gap-3">
                  <span class="ttag"><i class="fas fa-fire me-1"></i>Découvrez nos Recettes Authentiques</span>
                  <div class="tsoc">
                     <a href="#"><i class="fab fa-facebook-f"></i></a>
                     <a href="#"><i class="fab fa-instagram"></i></a>
                     <a href="#"><i class="fab fa-tiktok"></i></a>
                     <a href="#"><i class="fab fa-youtube"></i></a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- ============================================================
         NAVBAR
         ============================================================ -->
      <nav class="navbar navbar-expand-lg" id="nav">
         <div class="container">
            <a class="navbar-brand" href="index.php">
               <div class="blogo">
                  <div class="bico"><i class="fas fa-leaf"></i></div>
                  <div>
                     <div class="bname">Goûts<span>Bénin</span></div>
                     <div class="bsub">Blog Culinaire & Recettes</div>
                  </div>
               </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <i class="fas fa-bars" style="color:var(--primary);font-size:1.35rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navmenu">
               <ul class="navbar-nav mx-auto">
                  <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                  <li class="nav-item"><a class="nav-link" href="recettes.php">Recettes</a></li>
                  <li class="nav-item"><a class="nav-link" href="index.php#about">À Propos</a></li>
                  <li class="nav-item"><a class="nav-link" href="index.php#testimonials">Avis</a></li>
                  <li class="nav-item"><a class="nav-link" href="index.php#contact-section">Contact</a></li>
               </ul>
               <div class="d-flex align-items-center gap-1">
                  <button id="navSearchBtn" title="Rechercher"><i class="fas fa-search"></i></button>
                  <div id="nav-auth-btn-placeholder" class="d-flex align-items-center gap-1">
                     <?php if (isLoggedIn()): ?>
                        <a href="profil.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i><?= e(currentUserName() ?? 'Profil') ?></a>
                        <a href="../actions/auth_logout.php" class="nav-link text-danger">Déconnexion</a>
                     <?php else: ?>
                        <a href="login.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i>Connexion</a>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </nav>
      <!-- ============================================================
         SEARCH OVERLAY POPUP
         ============================================================ -->
      <div id="searchOv">
         <button class="sovclose" id="searchClose"><i class="fas fa-times"></i></button>
         <div class="sovbox">
            <h4>Que recherchez-vous aujourd'hui ?</h4>
            <div class="sovinput">
               <input type="text" id="searchInput" placeholder="Rechercher amiwo, gbégbé, sauce graine..." autocomplete="off"/>
               <button><i class="fas fa-search"></i></button>
            </div>
            <div class="sovcats">
               <div class="sovcat active" data-cat="all">
                  <img src="img/menu/1.jpg" alt=""/>Toutes
               </div>
               <div class="sovcat" data-cat="plats">
                  <img src="img/menu/1.jpg" alt=""/>Plats
               </div>
               <div class="sovcat" data-cat="soupes">
                  <img src="img/menu/2.jpg" alt=""/>Soupes
               </div>
               <div class="sovcat" data-cat="entrees">
                  <img src="img/menu/3.jpg" alt=""/>Entrées
               </div>
               <div class="sovcat" data-cat="boissons">
                  <img src="img/menu/4.jpg" alt=""/>Boissons
               </div>
               <div class="sovcat" data-cat="desserts">
                  <img src="img/menu/6.jpg" alt=""/>Desserts
               </div>
            </div>
         </div>
      </div>

      <!-- ============================================================
         SUBMIT HEADER
         ============================================================ -->
      <section class="submit-header">
         <div class="container text-center">
            <h1 class="display-5 fw-bold text-white">Partager une Recette</h1>
            <p class="text-white-50">Publiez vos créations et faites découvrir les saveurs du Bénin au monde entier.</p>
         </div>
      </section>

      <!-- ============================================================
         SUBMIT FORM
         ============================================================ -->
      <section class="py-5" style="background: var(--bg-color);">
         <div class="container">
            <div class="row justify-content-center">
               <div class="col-lg-10" data-aos="fade-up">
                  <div class="form-card">
                     <h3 class="fw-bold mb-4" style="color: var(--dark);"><i class="fas fa-clipboard-list text-danger me-2"></i>Informations sur la recette</h3>
                     
                     <form id="submitRecipeForm" action="../actions/recette_submit.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <div class="row g-4">
                           <!-- Titre -->
                           <div class="col-md-8">
                              <div class="form-group">
                                 <label for="recipeTitleInput" class="form-label-custom">Titre de la recette</label>
                                 <input type="text" class="form-control form-control-custom" id="recipeTitleInput" name="titre" placeholder="Ex: Sauce Graine au poisson fumé" required>
                              </div>
                           </div>

                           <!-- Catégorie -->
                           <div class="col-md-4">
                              <div class="form-group">
                                 <label for="recipeCategorySelect" class="form-label-custom">Catégorie</label>
                                 <select class="form-select form-control-custom" id="recipeCategorySelect" name="id_categorie" required>
                                    <option value="" disabled selected>Choisir une catégorie</option>
                                    <?php foreach ($categories as $cat): ?>
                                       <option value="<?= e($cat['id']) ?>"><?= e($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                 </select>
                              </div>
                           </div>

                           <!-- Description -->
                           <div class="col-12">
                              <div class="form-group">
                                 <label for="recipeDescInput" class="form-label-custom">Courte description</label>
                                 <textarea class="form-control form-control-custom" id="recipeDescInput" name="description" rows="3" placeholder="Présentez brièvement l'histoire ou le goût de cette recette..." required></textarea>
                              </div>
                           </div>

                           <!-- Difficulté et temps -->
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipeDifficultySelect" class="form-label-custom">Difficulté</label>
                                 <select class="form-select form-control-custom" id="recipeDifficultySelect" name="difficulte" required>
                                    <option value="facile">Facile</option>
                                    <option value="moyen" selected>Moyen</option>
                                    <option value="difficile">Difficile</option>
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipePrepInput" class="form-label-custom">Temps prép (min)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipePrepInput" name="temps_prep" min="1" placeholder="Ex: 20" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipeCookInput" class="form-label-custom">Temps cuisson (min)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipeCookInput" name="temps_cuisson" min="0" placeholder="Ex: 30" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label for="recipePortionsInput" class="form-label-custom">Portions (pers.)</label>
                                 <input type="number" class="form-control form-control-custom" id="recipePortionsInput" name="portion" min="1" placeholder="Ex: 4" required>
                              </div>
                           </div>

                           <!-- Ingrédients -->
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="recipeIngredientsInput" class="form-label-custom">Ingrédients (un par ligne)</label>
                                 <textarea class="form-control form-control-custom" id="recipeIngredientsInput" name="ingredients" rows="8" placeholder="500g de farine de maïs&#10;2 tomates fraîches&#10;1 oignon rouge&#10;Du poisson fumé" required></textarea>
                              </div>
                           </div>

                           <!-- Étapes -->
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="recipeStepsInput" class="form-label-custom">Étapes de préparation (une par ligne)</label>
                                 <textarea class="form-control form-control-custom" id="recipeStepsInput" name="etapes" rows="8" placeholder="Écraser les tomates et les oignons.&#10;Faire cuire le mélange dans une casserole.&#10;Ajouter l'eau de cuisson et les poissons." required></textarea>
                              </div>
                           </div>

                           <!-- Vidéo YouTube -->
                           <div class="col-12">
                              <div class="form-group">
                                 <label for="recipeVideoInput" class="form-label-custom">URL vidéo YouTube</label>
                                 <input type="url" class="form-control form-control-custom" id="recipeVideoInput" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                                 <small class="text-muted">Optionnel : lien vers une vidéo de préparation.</small>
                              </div>
                           </div>

                           <!-- Image Upload -->
                           <div class="col-12">
                              <div class="form-group">
                                 <label class="form-label-custom">Image de présentation</label>
                                 <div class="img-preview-box" id="imageBox">
                                    <i class="far fa-image"></i>
                                    <span class="text-secondary small fw-semibold">Cliquez pour choisir ou glisser-déposer une image</span>
                                    <span class="text-muted small mt-1">Formats acceptés : JPG, PNG, WEBP (Max 5Mo)</span>
                                    <img src="#" id="imagePreview" alt="Prévisualisation"/>
                                 </div>
                                 <input type="file" id="recipeImageFile" name="image" accept="image/*" class="d-none">
                              </div>
                           </div>

                           <!-- Submit message -->
                           <div class="col-12" id="formMessage"></div>

                           <!-- Buttons -->
                           <div class="col-12 d-flex justify-content-end gap-3 mt-4 border-top pt-4">
                              <a href="profil.php" class="btn btn-outline-dark px-4 py-2">Annuler</a>
                              <button type="submit" class="btn btn-danger px-5 py-2 fw-semibold" id="submitBtn"><i class="fas fa-paper-plane me-1"></i> Soumettre la recette</button>
                           </div>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>

      <!-- FOOTER -->
      <footer>
         <div class="container">
            <div class="row g-5">
               <div class="col-lg-4">
                  <div class="fnm">Goûts<span>Bénin</span></div>
                  <p class="fdesc">Explorez les recettes béninoises, simples et savoureuses, préparées avec des ingrédients locaux et du cœur.</p>
                  <div class="fsoc">
                     <a href="#"><i class="fab fa-facebook-f"></i></a>
                     <a href="#"><i class="fab fa-instagram"></i></a>
                     <a href="#"><i class="fab fa-twitter"></i></a>
                     <a href="#"><i class="fab fa-youtube"></i></a>
                     <a href="#"><i class="fab fa-tiktok"></i></a>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-2">
                  <div class="ftit">Liens rapides</div>
                  <ul class="flinks ps-0">
                     <li><a href="index.php"><i class="fas fa-chevron-right"></i>Accueil</a></li>
                     <li><a href="index.php#about"><i class="fas fa-chevron-right"></i>À propos</a></li>
                     <li><a href="recettes.php"><i class="fas fa-chevron-right"></i>Recettes</a></li>
                     <li><a href="index.php#testimonials"><i class="fas fa-chevron-right"></i>Avis</a></li>
                     <li><a href="index.php#contact-section"><i class="fas fa-chevron-right"></i>Contact</a></li>
                  </ul>
               </div>
               <div class="col-sm-6 col-lg-2">
                  <div class="ftit">Catégories</div>
                  <ul class="flinks ps-0">
                     <li><a href="recettes.php?categorie=plats-principaux"><i class="fas fa-chevron-right"></i>Plats principaux</a></li>
                     <li><a href="recettes.php?categorie=soupes-sauces"><i class="fas fa-chevron-right"></i>Soupes</a></li>
                     <li><a href="recettes.php?categorie=entrees"><i class="fas fa-chevron-right"></i>Entrées</a></li>
                     <li><a href="recettes.php?categorie=boissons"><i class="fas fa-chevron-right"></i>Boissons</a></li>
                     <li><a href="recettes.php?categorie=desserts"><i class="fas fa-chevron-right"></i>Desserts</a></li>
                  </ul>
               </div>
               <div class="col-lg-4">
                  <div class="ftit">Nous contacter</div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-map-marker-alt"></i></div>
                     <div class="fciinfo"><strong>Adresse</strong>Cotonou, Bénin</div>
                  </div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-phone-alt"></i></div>
                     <div class="fciinfo"><strong>Téléphone</strong>+229 90 12 34 56</div>
                  </div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-envelope"></i></div>
                     <div class="fciinfo"><strong>Email</strong>contact@goutsbenin.org</div>
                  </div>
               </div>
            </div>
         </div>
         <div class="fbot">
            <div class="container">
               <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <p>&copy; 2026 <span>GoûtsBénin</span>. Tous droits réservés.</p>
                  <div><a href="#">Confidentialité</a><a href="#">Conditions</a></div>
               </div>
            </div>
         </div>
      </footer>

      <button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-chevron-up"></i></button>
    
      <!-- jQuery -->
      <script src="js/jquery-3.7.1.min.js"></script>
      <!-- Bootstrap 5 -->
      <script src="js/bootstrap.bundle.min.js"></script>
      <!-- AOS -->
      <script src="js/aos.js"></script>
      <!-- Swiper -->
      <script src="js/swiper-bundle.min.js"></script>
      <!-- CounterUp -->
      <script src="js/jquery.magnific-popup.min.js"></script>
      <!-- Main js -->
      <script src="js/main.js"></script>
      <script src="js/main.js"></script>
   </body>
</html>

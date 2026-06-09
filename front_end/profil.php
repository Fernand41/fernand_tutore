<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getInstance();

requireAuth('login.php');

$userName = currentUserName() ?? 'Utilisateur';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole  = currentUserRole() ?? 'Membre';

$favorites = [];
$myRecipes = [];
try {
    $stmt = $pdo->prepare('SELECT r.id, r.slug, r.titre, r.image, r.statut FROM favoris f JOIN recettes r ON f.id_recette = r.id WHERE f.id_utilisateur = ? AND r.statut = "publie" ORDER BY f.date_ajout DESC LIMIT 6');
    $stmt->execute([currentUserId()]);
    $favorites = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT r.id, r.slug, r.titre, r.image, r.statut FROM recettes r WHERE r.id_auteur = ? ORDER BY r.date_creation DESC LIMIT 8');
    $stmt->execute([currentUserId()]);
    $myRecipes = $stmt->fetchAll();
} catch (Exception $e) {
    // Si la table favoris n'existe pas ou qu'il y a une erreur, on ignore.
    $favorites = [];
    $myRecipes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="GoûtsBénin">
      <meta name="description" content="Mon espace personnel sur Goûts du Bénin - Retrouvez vos favoris et soumettez des recettes.">
      <title>Mon Profil - GoûtsBénin</title>
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
         .profile-header {
            position: relative;
            overflow: hidden;
            padding: 140px 0 80px 0;
            color: #fff;
         }
         .profile-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1500&q=80') center / cover no-repeat;
            filter: blur(5px) brightness(0.55);
            transform: scale(1.05);
            z-index: 1;
         }
         .profile-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(0,0,0,0.1), rgba(0,0,0,0.6) 60%, rgba(0,0,0,0.8));
            z-index: 2;
         }
         .profile-header .container {
            position: relative;
            z-index: 3;
         }
         .profile-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
            margin-top: -40px;
            position: relative;
            z-index: 10;
         }
         .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2.5rem;
            box-shadow: 0 10px 25px rgba(230, 92, 92, 0.3);
            margin: 0 auto 20px auto;
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
         PROFILE HERO
         ============================================================ -->
      <section class="profile-header">
         <div class="container text-center">
            <h1 class="display-5 fw-bold text-white">Espace Personnel</h1>
            <p class="text-white-50">Gérez vos informations et retrouvez vos recettes béninoises favorites.</p>
         </div>
      </section>

      <!-- ============================================================
         PROFILE CONTENT
         ============================================================ -->
      <section class="py-5" style="background: var(--bg-color);">
         <div class="container">
            <div class="row g-4">
               <!-- Left Sidebar Profile Info -->
               <div class="col-lg-4" data-aos="fade-right">
                  <div class="profile-card text-center">
                     <div class="profile-avatar-large"><?= strtoupper(substr(e($userName), 0, 1)) ?></div>
                     <h3 class="fw-bold mb-1"><?= e($userName) ?></h3>
                     <p class="text-muted mb-3"><?= e($userEmail) ?></p>
                     <span class="badge bg-dark px-3 py-2 mb-4"><?= e(ucfirst($userRole)) ?></span>
                     
                     <div class="d-grid gap-2 border-top pt-4">
                        <a href="soumettre-recette.php" class="btn btn-danger py-2 fw-semibold"><i class="fas fa-plus-circle me-1"></i> Proposer une recette</a>
                        <a href="../actions/auth_logout.php" class="btn btn-outline-dark py-2"><i class="fas fa-sign-out-alt me-1"></i> Se déconnecter</a>
                     </div>
                  </div>
               </div>

               <!-- Right Area Favorites -->
               <div class="col-lg-8" data-aos="fade-left">
                  <div class="bg-white rounded-4 p-4 p-sm-5 shadow-sm border border-light">
                     <h3 class="fw-bold mb-4" style="color: var(--dark);"><i class="fas fa-heart text-danger me-2"></i>Mes Recettes Favorites</h3>
                     
                     <!-- Grid for Favorites -->
                     <div class="row g-4" id="favoritesGrid">
                        <?php if (!empty($favorites)): ?>
                           <?php foreach ($favorites as $favorite): ?>
                              <?php $favImage = $favorite['image'] ? 'uploads/recettes/' . $favorite['image'] : 'img/menu/1.jpg'; ?>
                              <div class="col-md-6">
                                 <div class="card border-0 shadow-sm">
                                    <img src="<?= e($favImage) ?>" class="card-img-top" alt="<?= e($favorite['titre']) ?>" style="height:180px;object-fit:cover;"/>
                                    <div class="card-body">
                                       <h6 class="fw-semibold mb-2"><?= e($favorite['titre']) ?></h6>
                                       <a href="recette.php?slug=<?= e($favorite['slug']) ?>" class="text-danger text-decoration-none">Voir la recette</a>
                                    </div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <div class="col-12 text-center py-5">
                              <p class="mb-0 text-muted">Vous n'avez pas encore de recettes favorites.</p>
                           </div>
                        <?php endif; ?>
                     </div>
                  </div>

                  <div class="bg-white rounded-4 p-4 p-sm-5 shadow-sm border border-light mt-4">
                     <h3 class="fw-bold mb-4" style="color: var(--dark);"><i class="fas fa-book text-danger me-2"></i>Mes recettes soumises</h3>
                     <?php if (!empty($myRecipes)): ?>
                        <div class="row g-4">
                           <?php foreach ($myRecipes as $recipe): ?>
                              <?php $recipeImage = $recipe['image'] ? 'uploads/recettes/' . $recipe['image'] : 'img/menu/1.jpg'; ?>
                              <div class="col-md-6">
                                 <div class="card border-0 shadow-sm">
                                    <img src="<?= e($recipeImage) ?>" class="card-img-top" alt="<?= e($recipe['titre']) ?>" style="height:180px;object-fit:cover;"/>
                                    <div class="card-body">
                                       <h6 class="fw-semibold mb-2"><?= e($recipe['titre']) ?></h6>
                                       <p class="small text-muted mb-2">Statut : <?= e(ucfirst($recipe['statut'])) ?></p>
                                       <div class="d-flex gap-2 flex-wrap">
                                          <a href="recette.php?id=<?= (int) $recipe['id'] ?>" class="btn btn-outline-danger btn-sm">Voir</a>
                                          <a href="modifier-recette.php?id=<?= (int) $recipe['id'] ?>" class="btn btn-danger btn-sm">Modifier</a>
                                          <form action="../actions/recette_delete.php" method="POST" class="d-inline">
                                             <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                             <input type="hidden" name="id_recette" value="<?= (int) $recipe['id'] ?>">
                                             <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Supprimer cette recette ?');">Supprimer</button>
                                          </form>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        </div>
                     <?php else: ?>
                        <p class="text-muted">Vous n'avez encore soumis aucune recette.</p>
                     <?php endif; ?>
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
   </body>
</html>

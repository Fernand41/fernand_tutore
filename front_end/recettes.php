<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
$pdo = Database::getInstance();

$selectedCategory = trim($_GET['categorie'] ?? '');
$params = [];
$sql = 'SELECT r.id, r.slug, r.titre, r.description, r.image, r.nb_personnes, r.temps_prep, r.temps_cuisson, r.difficulte, c.nom AS categorie, c.slug AS categorie_slug
        FROM recettes r
        JOIN categories_recettes c ON r.id_categorie = c.id
        WHERE r.statut = "publie"';
if ($selectedCategory !== '' && $selectedCategory !== 'all') {
    $sql .= ' AND c.slug = ?';
    $params[] = $selectedCategory;
}
$sql .= ' ORDER BY r.date_publication DESC, r.date_creation DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recettes = $stmt->fetchAll();
$categories = $pdo->query('SELECT nom, slug FROM categories_recettes ORDER BY nom')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="GoûtsBénin">
      <meta name="description" content="GoûtsBénin - Blog Culinaire & Recettes Traditionnelles Béninoises">
      <title>GoûtsBénin - Blog Culinaire & Recettes Traditionnelles</title>
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
   </head>
   <body>
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
                  <li class="nav-item"><a class="nav-link active" href="#hero">Accueil</a></li>
                  <li class="nav-item"><a class="nav-link" href="#category">Catégories</a></li>
                  <li class="nav-item"><a class="nav-link" href="#menu">Dernières Recettes</a></li>
                  <li class="nav-item"><a class="nav-link" href="#about">À Propos</a></li>
                  <li class="nav-item"><a class="nav-link" href="#testimonials">Avis</a></li>
                  <li class="nav-item"><a class="nav-link" href="#contact-section">Contact</a></li>
               </ul>
               <div class="d-flex align-items-center gap-1">
                  <!-- FIX 1: Search button -->
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
         FIX 1 � SEARCH OVERLAY POPUP
         ============================================================ -->
      <div id="searchOv">
         <button class="sovclose" id="searchClose"><i class="fas fa-times"></i></button>
         <div class="sovbox">
            <h4>Que recherchez-vous aujourd'hui ?</h4>
            <div class="sovinput">
               <input type="text" id="searchInput" placeholder="Rechercher amiwo, gbégbé, sauce graine..." autocomplete="off"/>
               <button><i class="fas fa-search"></i></button>
            </div>
            <!-- Categories inside search box -->
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
            <div class="sovtrend">
               <p><i class="fas fa-fire me-1" style="color:var(--secondary);"></i>Recettes populaires</p>
               <span class="ttag">Amiwo</span>
               <span class="ttag">Gbégbé</span>
               <span class="ttag">Sauce Graine</span>
               <span class="ttag">Akassa</span>
               <span class="ttag">Abará</span>
               <span class="ttag">Kpétégouma</span>
            </div>
           <!-- ============================================================
         RECIPES LISTING HERO
         ============================================================ -->
      <section id="hero" style="min-height: auto; padding: 120px 0 60px 0; background: linear-gradient(135deg, var(--dark), #151515); position: relative;">
         <div class="hs hs1"></div>
         <div class="hs hs2"></div>
         <div class="container text-center" style="position: relative; z-index: 2;">
            <div class="hbadge mx-auto mb-3" style="width: max-content;">
               <div class="hbi"><i class="fas fa-search"></i></div>
               <span>Toutes nos Recettes</span>
            </div>
            <h1 class="htitle text-center" style="font-size: 3rem; margin-bottom: 12px; line-height: 1.2;">Explorez la <span class="hl">Gastronomie</span> Béninoise</h1>
            <p class="hdesc mx-auto" style="max-width: 600px; font-size: 1.05rem;">Découvrez notre sélection de recettes traditionnelles authentiques. Utilisez les filtres ci-dessous pour trouver l'inspiration.</p>
         </div>
      </section>

      <!-- ============================================================
         RECIPES LISTING & FILTERS
         ============================================================ -->
      <section id="menu" style="padding: 60px 0 80px 0; background: var(--bg-color);">
         <div class="container">
            <!-- Filter Bar -->
            <div class="row g-3 mb-5 align-items-center bg-dark p-4 rounded-3 shadow" style="border: 1px solid rgba(255,255,255,0.05); margin-top: -30px; position: relative; z-index: 10;">
               <div class="col-md-4">
                  <label class="text-white mb-2 small" style="font-weight: 500;"><i class="fas fa-search me-1 text-danger"></i> Rechercher</label>
                  <input type="text" id="recipesSearchInput" class="fctrl" placeholder="Nom de recette, ingrédient..."/>
               </div>
               <div class="col-md-4">
                  <label class="text-white mb-2 small" style="font-weight: 500;"><i class="fas fa-filter me-1 text-danger"></i> Catégorie</label>
                  <select id="recipesCategorySelect" name="categorie" class="fctrl">
                     <option value="all"<?= $selectedCategory === 'all' ? ' selected' : '' ?>>Toutes les catégories</option>
                     <?php foreach ($categories as $categorie): ?>
                        <option value="<?= e($categorie['slug']) ?>"<?= $selectedCategory === $categorie['slug'] ? ' selected' : '' ?>><?= e($categorie['nom']) ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="col-md-4">
                  <label class="text-white mb-2 small" style="font-weight: 500;"><i class="fas fa-signal me-1 text-danger"></i> Difficulté</label>
                  <select id="recipesDifficultySelect" class="fctrl">
                     <option value="">Toutes les difficultés</option>
                     <option value="facile">Facile</option>
                     <option value="moyen">Moyen</option>
                     <option value="difficile">Difficile</option>
                  </select>
               </div>
            </div>

            <!-- Recipes Grid -->
            <div class="row g-4" id="mgrid">
               <?php if (!empty($recettes)): ?>
                  <?php foreach ($recettes as $recette): ?>
                     <?php $recipeImage = $recette['image'] ? '../uploads/recettes/' . $recette['image'] : 'img/menu/1.jpg'; ?>
                     <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0">
                           <img src="<?= e($recipeImage) ?>" class="card-img-top" alt="<?= e($recette['titre']) ?>" style="height:220px;object-fit:cover;"/>
                           <div class="card-body d-flex flex-column">
                              <span class="badge bg-danger mb-2"><?= e($recette['categorie']) ?></span>
                              <h5 class="card-title"><?= e($recette['titre']) ?></h5>
                              <p class="card-text text-muted mb-3" style="flex:1;"><?= e(mb_strimwidth($recette['description'], 0, 100, '...')) ?></p>
                              <a href="recette.php?slug=<?= e($recette['slug']) ?>" class="btn btn-danger btn-sm mt-auto">Voir la recette</a>
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="col-12 text-center">
                     <p>Aucune recette ne correspond à ce filtre.</p>
                  </div>
               <?php endif; ?>
            </div>

            <!-- Pagination -->
            <nav class="mt-5">
               <ul class="pagination justify-content-center" id="recettes-pagination">
                  <!-- Boutons de pagination injectés par recettes.js -->
               </ul>
            </nav>
         </div>
      </section>

      <!-- NEWSLETTER -->
      <section id="newsletter" style="background: linear-gradient(135deg, var(--dark), #111); padding: 70px 0;">
         <div class="container">
            <div class="nlw text-center" data-aos="zoom-in">
               <span class="slbl" style="color:rgba(255,255,255,.7);">Restez informé</span>
               <h2 class="mb-3" style="color:#fff;">Recevez nos <span>nouveautés</span></h2>
               <p class="mb-4" style="color:rgba(255,255,255,.78);">Abonnez-vous pour découvrir les nouvelles recettes et les astuces culinaires du Bénin.</p>
               <div class="nl-form-wrap">
                  <input class="nlinput" type="email" id="nlEmail" placeholder="Votre adresse email..."/>
                  <button class="nlbtn" id="nlBtn"><i class="fas fa-paper-plane me-1"></i>S'abonner</button>
               </div>
               <p style="color:rgba(255,255,255,.45);font-size:.76rem;margin-top:11px;"><i class="fas fa-lock me-1"></i>Pas de spam, vous pouvez vous désabonner à tout moment.</p>
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
                     <li><a href="#hero"><i class="fas fa-chevron-right"></i>Accueil</a></li>
                     <li><a href="#about"><i class="fas fa-chevron-right"></i>À propos</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Recettes</a></li>
                     <li><a href="#reservation"><i class="fas fa-chevron-right"></i>Partager</a></li>
                     <li><a href="#blog"><i class="fas fa-chevron-right"></i>Actualités</a></li>
                     <li><a href="#contact-section"><i class="fas fa-chevron-right"></i>Contact</a></li>
                  </ul>
               </div>
               <div class="col-sm-6 col-lg-2">
                  <div class="ftit">Catégories</div>
                  <ul class="flinks ps-0">
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Plats principaux</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Soupes</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Entrées</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Boissons</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Desserts</a></li>
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
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-clock"></i></div>
                     <div class="fciinfo"><strong>Horaires</strong>Mer - Sam: 09:00 - 19:00</div>
                  </div>
               </div>
            </div>
         </div>
         <div class="fbot">
            <div class="container">
               <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <p>&copy; 2026 <span>GoûtsBénin</span>. Tous droits réservés.</p>
                  <div><a href="#">Confidentialité</a><a href="#">Conditions</a><a href="#">Cookies</a></div>
               </div>
            </div>
         </div>
      </footer>
      <!-- Floating cart -->
      <!-- <div class="cartfl"><i class="fas fa-shopping-cart"></i><span>My Cart</span><div class="ccount" id="cartCount">0</div></div> -->
      <!-- Back to top -->
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

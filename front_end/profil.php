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
            padding: 120px 0 60px 0;
            background: linear-gradient(135deg, var(--dark), #151515);
            color: #fff;
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
                     <a href="login.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i>Connexion</a>
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
                     <div class="profile-avatar-large" id="profileAvatar">U</div>
                     <h3 class="fw-bold mb-1" id="profileName">Chargement...</h3>
                     <p class="text-muted mb-3" id="profileEmail">...</p>
                     <span class="badge bg-dark px-3 py-2 mb-4" id="profileRole">Membre</span>
                     
                     <div class="d-grid gap-2 border-top pt-4">
                        <a href="soumettre-recette.php" class="btn btn-danger py-2 fw-semibold"><i class="fas fa-plus-circle me-1"></i> Proposer une recette</a>
                        <button class="btn btn-outline-dark py-2" id="profileLogoutBtn"><i class="fas fa-sign-out-alt me-1"></i> Se déconnecter</button>
                     </div>
                  </div>
               </div>

               <!-- Right Area Favorites -->
               <div class="col-lg-8" data-aos="fade-left">
                  <div class="bg-white rounded-4 p-4 p-sm-5 shadow-sm border border-light">
                     <h3 class="fw-bold mb-4" style="color: var(--dark);"><i class="fas fa-heart text-danger me-2"></i>Mes Recettes Favorites</h3>
                     
                     <!-- Grid for Favorites -->
                     <div class="row g-4" id="favoritesGrid">
                        <!-- Recettes favorites injectées dynamiquement -->
                        <div class="col-12 text-center py-5">
                           <div class="spinner-border text-danger" role="status">
                              <span class="visually-hidden">Chargement...</span>
                           </div>
                        </div>
                     </div>
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
      <script src="js/api.js"></script>
      <script src="js/auth.js"></script>
      <script src="js/profil.js"></script>
   </body>
</html>

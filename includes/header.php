<?php
/**
 * includes/header.php
 * En-tête commun du FRONT-END : <head> + topbar + navbar
 *
 * Variables attendues (optionnelles, définissables avant l'include) :
 *   $pageTitle      — titre de l'onglet (défaut : "GoûtsBénin")
 *   $pageDescription— meta description
 *   $activePage     — identifiant pour marquer le lien actif dans la nav
 *                     valeurs : 'accueil' | 'recettes' | 'a-propos' | 'contact'
 */

require_once __DIR__ . '/session.php';

$pageTitle       = $pageTitle       ?? 'GoûtsBénin – Blog Culinaire & Recettes Traditionnelles';
$pageDescription = $pageDescription ?? 'Découvrez les recettes béninoises authentiques, préparées avec des ingrédients locaux.';
$activePage      = $activePage      ?? '';

// Chemin relatif vers le dossier assets du front (css, js, img)
// Depuis pages/  → ../front_assets/
// Ajuste si tu changes la structure
$assetsPath = $assetsPath ?? '../front_assets';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="author" content="GoûtsBénin">
   <meta name="description" content="<?= e($pageDescription) ?>">
   <title><?= e($pageTitle) ?></title>

   <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet"/>
   <!-- Bootstrap 5.3 -->
   <link href="<?= $assetsPath ?>/css/bootstrap.min.css" rel="stylesheet"/>
   <!-- AOS Animate on Scroll -->
   <link href="<?= $assetsPath ?>/css/aos.css" rel="stylesheet"/>
   <!-- Swiper -->
   <link href="<?= $assetsPath ?>/css/swiper-bundle.min.css" rel="stylesheet"/>
   <!-- Font Awesome -->
   <link rel="stylesheet" href="<?= $assetsPath ?>/css/all.min.css"/>
   <!-- Magnific Popup -->
   <link rel="stylesheet" href="<?= $assetsPath ?>/css/magnific-popup.css"/>
   <!-- Style principal -->
   <link rel="stylesheet" href="<?= $assetsPath ?>/css/style.css"/>
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
         <a class="navbar-brand" href="<?= $assetsPath !== '../front_assets' ? 'index.php' : 'index.php' ?>">
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
               <li class="nav-item">
                  <a class="nav-link <?= $activePage === 'accueil' ? 'active' : '' ?>" href="index.php">Accueil</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link <?= $activePage === 'recettes' ? 'active' : '' ?>" href="recettes.php">Recettes</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link <?= $activePage === 'a-propos' ? 'active' : '' ?>" href="index.php#about">À Propos</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="index.php#contact-section">Contact</a>
               </li>
            </ul>

            <div class="d-flex align-items-center gap-1">
               <button id="navSearchBtn" title="Rechercher"><i class="fas fa-search"></i></button>

               <?php if (isLoggedIn()): ?>
                  <!-- Utilisateur connecté -->
                  <div class="dropdown">
                     <a href="#" class="nav-link nav-cta dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?= e(currentUserName()) ?>
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-id-card me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="soumettre.php"><i class="fas fa-plus-circle me-2"></i>Soumettre une recette</a></li>
                        <?php if (isAdmin()): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-success" href="../admin/index.php"><i class="fas fa-cog me-2"></i>Administration</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../actions/auth_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                     </ul>
                  </div>
               <?php else: ?>
                  <!-- Non connecté -->
                  <a href="login.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i>Connexion</a>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </nav>

   <!-- Barre de recherche dépliable -->
   <div id="searchBar" style="display:none; background:#f8f9fa; padding:12px 0; border-bottom:1px solid #ddd;">
      <div class="container">
         <form action="recettes.php" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Rechercher une recette béninoise..." value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit" class="btn btn-success px-4"><i class="fas fa-search"></i></button>
         </form>
      </div>
   </div>

   <script>
   // Toggle barre de recherche
   document.addEventListener('DOMContentLoaded', function() {
      var btn = document.getElementById('navSearchBtn');
      var bar = document.getElementById('searchBar');
      if (btn && bar) {
         btn.addEventListener('click', function() {
            bar.style.display = bar.style.display === 'none' ? 'block' : 'none';
            if (bar.style.display === 'block') {
               bar.querySelector('input[name="q"]').focus();
            }
         });
      }
   });
   </script>
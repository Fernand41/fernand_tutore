<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
$pdo = Database::getInstance();

$categories = $pdo->query('
    SELECT c.id, c.nom, c.slug, COUNT(r.id) AS nb_recettes
    FROM categories_recettes c
    LEFT JOIN recettes r ON r.id_categorie = c.id AND r.statut = "publie"
    GROUP BY c.id, c.nom, c.slug
    ORDER BY c.nom
')->fetchAll();
$latestRecipes = $pdo->query(
    'SELECT r.id, r.slug, r.titre, r.description, r.image,
            r.note_moyenne, r.nb_notes, r.difficulte, r.temps_prep,
            c.nom AS categorie, c.slug AS categorie_slug
     FROM recettes r
     JOIN categories_recettes c ON r.id_categorie = c.id
     WHERE r.statut = "publie"
     ORDER BY r.date_publication DESC, r.date_creation DESC
     LIMIT 9'
)->fetchAll();
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
                     <a href="login.php" class="nav-link nav-cta"><i class="fas fa-user me-1"></i>Connexion</a>
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
         </div>
      </div>
      <!-- ============================================================
         HERO
         ============================================================ -->
      <section id="hero">
         <div class="hs hs1"></div>
         <div class="hs hs2"></div>
         <div class="hbgtxt">RECETTES</div>
         <div class="container">
            <div class="row align-items-center g-5" style="min-height:88vh;">
               <div class="col-lg-6">
                  <div class="hbadge">
                     <div class="hbi"><i class="fas fa-star"></i></div>
                     <span>Saveurs Authentiques du Bénin</span>
                  </div>
                  <h1 class="htitle">Découvrez les <span class="hl">Recettes Traditionnelles</span><br/>Béninoises</h1>
                  <p class="hdesc">Explorez notre collection de recettes authentiques. De l'amiwo au gbégbé, des plats savoureux qui racontent l'histoire culinaire du Bénin.</p>
                  <div class="d-flex flex-wrap gap-3 mb-2">
                     <a href="#menu" class="btn-red"><i class="fas fa-utensils"></i>Explorer les Recettes</a>
                     <!-- FIX 2: Magnific popup video trigger -->
					 <a href="https://www.youtube.com/watch?v=RXv_uIN6e-Y" class="magnific_popup btn-play popup-youtube">
						<div class="pico"><i class="fas fa-play"></i></div>
						<span>Notre Histoire</span>
					 </a>
                  </div>
                  <div class="hstats d-flex gap-3 flex-wrap mt-4">
                     <div class="hstat"><span class="snum">500<em>+</em></span><small>Recettes</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">1000<em>+</em></span><small>Utilisateurs</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">50<em>+</em></span><small>Contributeurs</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">100<em>%</em></span><small>Authentique</small></div>
                  </div>
               </div>
               <div class="col-lg-6">
                  <div style="position:relative;text-align:center;">
                     <div class="hcircle">
                        <img src="img/accueil.webp" alt="Plat Béninois"/>
                     </div>
                     <div class="fcard fc1">
                        <div class="fcoi r"><i class="fas fa-fire"></i></div>
                        <div><span class="fcnum">Populaire</span><span class="fcsm">Plus aimée</span></div>
                     </div>
                     <div class="fcard fc2">
                        <div class="fcoi y"><i class="fas fa-star"></i></div>
                        <div><span class="fcnum">4.9/5</span><span class="fcsm">Excellent</span></div>
                     </div>
                     <div class="fcard fc3">
                        <div class="fcoi g"><i class="fas fa-clock"></i></div>
                        <div><span class="fcnum">02 heure</span><span class="fcsm">En moyenne</span></div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- MARQUEE -->
      <div class="mqsec">
         <div class="mqtrack">
            <div class="mqitem"><i class="fas fa-circle"></i>Amiwo</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Gbégbé</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Pâte de Maïs</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Sauce Graine</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kpétégouma</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Fufu</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Gâteau de Maïs</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Amiwo</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Gbégbé</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Pâte de Maïs</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Sauce Graine</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kpétégouma</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Fufu</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Gâteau de Maïs</div>
         </div>
      </div>
      <!-- CATEGORY -->
      <section id="category">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Nos Catégories</span>
               <h2 class="stitle">Parcourir par <span>Catégorie</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">Découvrez les saveurs béninoises organisées par catégories. Chaque recette raconte une histoire</p>
            </div>
            <?php
            $catImages = [
               'plats-principaux' => 'img/menu/2.jpg',
               'soupes-sauces'    => 'img/menu/2.jpeg',
               'entrees'          => 'img/menu/entre.jpeg',
               'desserts'         => 'img/menu/desser.jpg',
               'boissons'         => 'img/menu/boisson.webp',
               'collations'       => 'img/menu/collation.jpg',
            ];
            ?>
            <div class="cat-row justify-content-center" data-aos="fade-up">
               <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $i => $categorie): ?>
                     <?php
                        $imgSrc = $catImages[$categorie['slug']] ?? 'img/menu/1.jpg';
                        $nb     = (int) $categorie['nb_recettes'];
                        $label  = $nb . ' recette' . ($nb > 1 ? 's' : '');
                     ?>
                     <a href="recettes.php?categorie=<?= e($categorie['slug']) ?>"
                        class="catcard <?= $i < 2 ? 'active' : '' ?>"
                        data-aos="fade-up" data-aos-delay="<?= $i * 60 ?>">
                        <img src="<?= e($imgSrc) ?>" alt="<?= e($categorie['nom']) ?>" class="catimg"/>
                        <div class="catnm"><?= e($categorie['nom']) ?></div>
                        <div class="catct"><?= $label ?></div>
                     </a>
                  <?php endforeach; ?>
               <?php else: ?>
                  <p class="text-center w-100">Aucune catégorie disponible pour le moment.</p>
               <?php endif; ?>
            </div>
         </div>
      </section>
      <!-- ABOUT -->
      <section id="about">
         <div class="container">
            <div class="row align-items-center g-5">
               <div class="col-lg-5" data-aos="fade-right">
                  <div class="astack">
                     <div class="aexp"><span class="anum">100%</span><small>Recettes<br/>Authentiques</small></div>
                     <div class="amain"><img src="img/about1.webp" alt="Cuisine Béninoise"/></div>
                     <div class="asm"><img src="img/about2.jpg" alt=""/></div>
                  </div>
               </div>
               <div class="col-lg-7" data-aos="fade-left">
                  <span class="slbl">Notre Histoire</span>
                  <h2 class="stitle text-start">GoûtsBénin : Préserver<br/>la Saveur <span>Authentique</span></h2>
                  <div class="sline lft"></div>
                  <p class="sdesc mb-4">GoûtsBénin est née d'une passion pour préserver et partager les recettes traditionnelles béninoises. Notre mission est de documenter chaque recette, chaque savoir-faire culinaire unique du Bénin pour les générations futures.</p>
                  <div class="mb-4">
                     <div class="fti">
                        <div class="ftico r"><i class="fas fa-leaf"></i></div>
                        <div>
                           <h6>100% Ingrédients Naturels</h6>
                           <p>Nous célébrons les ingrédients locaux du Bénin: manioc, maïs, arachides, et bien d'autres.</p>
                        </div>
                     </div>
                     <div class="fti">
                        <div class="ftico y"><i class="fas fa-award"></i></div>
                        <div>
                           <h6>Recettes Éprouvées</h6>
                           <p>Chaque recette provient des cuisines traditionnnelles béninoises et est testée avec soin.</p>
                        </div>
                     </div>
                     <div class="fti">
                        <div class="ftico g"><i class="fas fa-users"></i></div>
                        <div>
                           <h6>Communauté Engagée</h6>
                           <p>Notre communauté de contributeurs partage ses connaissances culinaires avec passion.</p>
                        </div>
                     </div>
                  </div>
                  <a href="#menu" class="btn-red"><i class="fas fa-book-open"></i>Lire les Recettes</a>
               </div>
            </div>
         </div>
      </section>

      <!-- ===== NOS RECETTES (menu style) ===== -->
      <?php
      /* Couleurs badge par catégorie */
      $catColors = [
         'plats-principaux' => '#e8281a',
         'soupes-sauces'    => '#e67e22',
         'entrees'          => '#27ae60',
         'desserts'         => '#8e44ad',
         'boissons'         => '#2980b9',
         'collations'       => '#16a085',
      ];
      /* Image placeholder par défaut si la recette n'a pas d'image */
      $placeholder = 'img/menu/1.jpg';
      ?>
      <section id="menu" style="background:var(--light); padding: 80px 0;">
         <div class="container">

            <!-- Titre -->
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Nos Recettes</span>
               <h2 class="stitle">Nos Délicieuses <span>Recettes</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:520px;">
                  Découvrez les meilleures recettes béninoises partagées par notre communauté et approuvées par l'équipe.
               </p>
            </div>

            <!-- Filtres par catégorie -->
            <div class="menu-filters d-flex flex-wrap justify-content-center gap-2 mb-5" data-aos="fade-up">
               <button class="mf-btn active" data-filter="all">Tout</button>
               <?php foreach ($categories as $cat): ?>
                  <?php if ((int)$cat['nb_recettes'] > 0): ?>
                     <button class="mf-btn" data-filter="<?= htmlspecialchars($cat['slug']) ?>">
                        <?= htmlspecialchars($cat['nom']) ?>
                     </button>
                  <?php endif; ?>
               <?php endforeach; ?>
            </div>

            <!-- Grille recettes -->
            <?php if (!empty($latestRecipes)): ?>
               <div class="menu-grid row g-4" id="menuGrid">
                  <?php foreach ($latestRecipes as $i => $r):
                     $img      = !empty($r['image']) ? '../uploads/recettes/' . htmlspecialchars($r['image']) : $placeholder;
                     $color    = $catColors[$r['categorie_slug']] ?? '#e8281a';
                     $stars    = round((float)$r['note_moyenne']);
                     $badges   = ['Hot', 'New', 'Best Seller', 'Populaire', 'Top'];
                     $badge    = $i < count($badges) ? $badges[$i] : null;
                  ?>
                  <div class="col-md-6 col-lg-4 menu-item" data-cat="<?= htmlspecialchars($r['categorie_slug']) ?>"
                       data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                     <div class="mcard">

                        <!-- Image -->
                        <div class="mcard-img-wrap">
                           <img src="<?= $img ?>" alt="<?= htmlspecialchars($r['titre']) ?>" class="mcard-img" loading="lazy"/>
                           <?php if ($badge): ?>
                              <span class="mcard-badge" style="background:<?= $color ?>">
                                 <i class="fas fa-star"></i> <?= $badge ?>
                              </span>
                           <?php endif; ?>
                           <!-- Bouton favoris visuel (décoratif) -->
                           <button class="mcard-fav" aria-label="Favoris"><i class="far fa-heart"></i></button>
                        </div>

                        <!-- Corps -->
                        <div class="mcard-body">
                           <span class="mcard-cat" style="color:<?= $color ?>"><?= htmlspecialchars(strtoupper($r['categorie'])) ?></span>
                           <h5 class="mcard-title"><?= htmlspecialchars($r['titre']) ?></h5>
                           <p class="mcard-desc"><?= htmlspecialchars(mb_substr($r['description'], 0, 90)) ?>…</p>

                           <!-- Footer : étoiles + bouton -->
                           <div class="mcard-footer">
                              <div class="mcard-stars">
                                 <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="<?= $s <= $stars ? 'fas' : 'far' ?> fa-star"></i>
                                 <?php endfor; ?>
                                 <?php if ($r['nb_notes'] > 0): ?>
                                    <span class="mcard-nb">(<?= (int)$r['nb_notes'] ?>)</span>
                                 <?php endif; ?>
                              </div>
                              <a href="recette.php?slug=<?= urlencode($r['slug']) ?>" class="mcard-plus" aria-label="Voir la recette">
                                 <i class="fas fa-plus"></i>
                              </a>
                           </div>
                        </div>

                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>

              

            <?php else: ?>
               <div class="text-center py-5">
                  <i class="fas fa-utensils fa-3x mb-3" style="color:var(--primary);opacity:.4;"></i>
                  <p class="text-muted">Aucune recette publiée pour le moment. Revenez bientôt !</p>
               </div>
            <?php endif; ?>

         </div>
      </section>
      <!-- ===== FIN NOS RECETTES ===== -->
      <section id="special">
         <div class="spbg"></div>
         <div class="container" style="position:relative;z-index:2;">
            <div class="row align-items-center g-5">
               <div class="col-lg-6" data-aos="fade-right">
                  <div class="sptag"><i class="fas fa-bolt me-1"></i>Recette du moment</div>
                  <h2 class="sptitle">Découvrez notre<br/><span>Plat</span> vedette du jour</h2>
                  <p class="spdesc">Profitez de notre recette recommandée pour cuisiner un plat béninois savoureux en famille ou entre amis.</p>
                  <div class="cdwrap">
                     <div class="cditem"><span class="cdnum" id="cdH">08</span><span class="cdlbl">Heures</span></div>
                     <div class="cditem"><span class="cdnum" id="cdM">45</span><span class="cdlbl">Minutes</span></div>
                     <div class="cditem"><span class="cdnum" id="cdS">30</span><span class="cdlbl">Secondes</span></div>
                  </div>
                  <a href="#menu" class="btn-red"><i class="fas fa-shopping-cart"></i>Voir la recette</a>
               </div>
               <div class="col-lg-6" data-aos="fade-left">
                  <div class="spimgw">
                     <div class="spglow"></div>
                     <div class="sppbdg"><span class="old">$24.99</span><span class="np">$17.49</span></div>
                     <img src="img/ablo.jpeg" alt="Special Burger"/>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
	  
      <!-- ============================================================
         GALLERY � FIX 7 (click opens detail popup)
         ============================================================ -->
      <section id="gallery">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Galerie Culinaire</span>
               <h2 class="stitle">Découvrez nos <span>Recettes</span></h2>
               <div class="sline"></div>
            </div>
            <div class="ggrid" data-aos="fade-up">
               <div class="gitem"
                  data-gi="0"
                  data-gimg="img/portfolio/work1.jpg"
                  data-gtitle="Amiwo"
                  data-gdesc="Recette traditionnelle de semoule de maïs accompagnée d'une sauce graine parfumée.">
                  <img src="img/portfolio/amiwo.jpeg" alt="Amiwo"/>
                  <div class="gover"><span><i class="fas fa-expand-alt"></i> Amiwo</span></div>
               </div>
               <div class="gitem"
                  data-gi="1"
                  data-gimg="img/portfolio/work2.jpg"
                  data-gtitle="Gbégbé"
                  data-gdesc="Purée de haricots noirs épicée, servie avec des plantains frits pour un goût riche et authentique.">
                  <img src="img/portfolio/wasawasa.jpg" alt="Gbégbé"/>
                  <div class="gover"><span><i class="fas fa-expand-alt"></i> wassawassa</span></div>
               </div>
               <div class="gitem"
                  data-gi="2"
                  data-gimg="img/portfolio/work3.jpg"
                  data-gtitle="Sauce Graine"
                  data-gdesc="Sauce onctueuse à base d'arachides, tomate et poisson, un classique du Sud du Bénin.">
                  <img src="img/portfolio/sauce_graine.jpeg" alt="Sauce Graine"/>
                  <div class="gover"><span><i class="fas fa-expand-alt"></i> Sauce Graine</span></div>
               </div>
               <div class="gitem"
                  data-gi="3"
                  data-gimg="img/portfolio/work4.jpg"
                  data-gtitle="Akassa"
                  data-gdesc="Pâte de maïs assouplie, servie avec des sauces et poissons fumés pour un repas traditionnel.">
                  <img src="img/portfolio/acassa.jpg" alt="Akassa"/>
                  <div class="gover"><span><i class="fas fa-expand-alt"></i> Akassa</span></div>
               </div>
               <div class="gitem"
                  data-gi="4"
                  data-gimg="img/portfolio/work5.jpg"
                  data-gtitle="Gâteau de Maïs"
                  data-gdesc="Dessert moelleux au maïs et noix de coco, parfait à partager en famille.">
                  <img src="img/portfolio/igname.jpg" alt="Gâteau de Maïs"/>
                  <div class="gover"><span><i class="fas fa-expand-alt"></i> igname</span></div>
               </div>
            </div>
         </div>
      </section>
      <!-- FIX 7 – GALLERY POPUP -->
      <div id="galPop">
         <div class="gpbox">
            <button class="gpclose" id="gpClose"><i class="fas fa-times"></i></button>
            <img id="gpImg" src="" alt=""/>
            <div class="gpcap">
               <h5 id="gpTitle"></h5>
               <p id="gpDesc"></p>
            </div>
            <div class="gpnav">
               <button id="gpPrev"><i class="fas fa-chevron-left me-1"></i>Prev</button>
               <button id="gpNext">Next <i class="fas fa-chevron-right ms-1"></i></button>
            </div>
         </div>
      </div>
	  
	  
      <!-- CHEFS -->
      <section id="chefs">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Notre équipe</span>
               <h2 class="stitle">Les <span>Contributeurs</span> GoûtsBénin</h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4">
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/chefs/keith.jpg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">keith SONON</div>
                        <div class="chrole">Collectrice de recettes</div>
                        <div class="chexp">Grand-mère béninoise</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="80">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/chefs/chef.jpg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">Georginia Viou</div>
                        <div class="chrole">Expert en sauces</div>
                        <div class="chexp">20 ans de cuisine</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="160">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/chefs/delphin.webp" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">Delphin Agbetorgan</div>
                        <div class="chrole">Spécialiste desserts</div>
                        <div class="chexp">15 ans de tradition</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="240">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/chefs/eden.jpg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">eden</div>
                        <div class="chrole">Animateur culinaire</div>
                        <div class="chexp">Cuisine du terroir</div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
	  
      <!-- HOURS -->
      <section id="hours">
         <div class="hrsbg"></div>
         <div class="container" style="position:relative;z-index:2;">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl" style="color:#a5d6bc;">Contact</span>
               <h2 class="stitle" style="color:#fff;">Rejoignez <span>GoûtsBénin</span></h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4 align-items-start">
               <div class="col-lg-5" data-aos="fade-right">
                  <div class="hrscard">
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Lundi - Mardi</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot off"></div>
                           <span class="hrstime" style="color:#ff6b6b;">Fermé</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Mercredi - Jeudi</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">09:00 - 18:00</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Vendredi</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">09:00 - 19:00</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Samedi</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">10:00 - 17:00</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Dimanche</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot off"></div>
                           <span class="hrstime">Fermé</span>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-3" data-aos="zoom-in">
                  <div class="hrscta">
                     <i class="fas fa-book-open fa-2x mb-3" style="color:rgba(255,255,255,.8);"></i>
                     <h4>Partagez une recette</h4>
                     <p>Vous avez une recette béninoise à partager ? Nous sommes à l'écoute.</p>
                     <a href="#reservation" class="btnw">Proposer maintenant</a>
                  </div>
               </div>
               <div class="col-lg-4" data-aos="fade-left">
                  <div class="hrscard">
                     <h5 style="color:#fff;margin-bottom:18px;font-family:'Poppins',sans-serif;font-size:.95rem;font-weight:700;"><i class="fas fa-map-marker-alt me-2" style="color:var(--secondary);"></i>Nous contacter</h5>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-location-dot me-2" style="color:var(--secondary);"></i>Adresse</span><span class="hrstime" style="font-size:.8rem;">Cotonou, Bénin</span></div>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-phone me-2" style="color:var(--secondary);"></i>Téléphone</span><span class="hrstime" style="font-size:.8rem;">+229 90 12 34 56</span></div>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-envelope me-2" style="color:var(--secondary);"></i>Email</span><span class="hrstime" style="font-size:.8rem;">contact@goutsbenin.org</span></div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
	  
      <!-- TESTIMONIALS -->
      <section id="testimonials">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Avis</span>
               <h2 class="stitle">Ce que disent <span>nos lecteurs</span></h2>
               <div class="sline"></div>
            </div>
            <div class="swiper tesSwiper" data-aos="fade-up">
               <div class="swiper-wrapper">
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">J'adore ces recettes ! Faciles à suivre et très proches de la cuisine de grand-mère. Merci pour le partage des recettes béninoises.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/1.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Mariam K.</div>
                              <div class="tesrl">Amatrice de cuisine</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">Grâce à GoûtsBénin, je cuisine enfin la sauce graine comme à la maison. Les explications sont claires et les ingrédients bien expliqués.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/2.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Idriss A.</div>
                              <div class="tesrl">Cuisinier amateur</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">Le site est une vraie mine d'or pour retrouver nos recettes traditionnelles. Les photos donnent envie et les astuces sont utiles.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/3.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Aminata S.</div>
                              <div class="tesrl">Maman de famille</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">J'aime le respect des traditions, et les recettes sont bien adaptées aux produits locaux du Bénin. À partager avec toute la famille.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/4.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Sébastien G.</div>
                              <div class="tesrl">Lecteur fidèle</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="swiper-pagination mt-4" style="position:static;"></div>
            </div>
         </div>
      </section>
	  
      <!-- RESERVATION FORM -->
      <section id="reservation">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Partagez</span>
               <h2 class="stitle">Proposez une <span>Recette</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">Envoyez votre recette béninoise préférée pour qu'elle rejoigne notre collection de plats traditionnels partagés.</p>
            </div>
            <div class="row g-4 align-items-start">
               <div class="col-lg-4" data-aos="fade-right">
                  <div style="background:var(--dark);border-radius:18px;padding:36px;">
                     <h4 style="color:#fff;font-size:1.3rem;margin-bottom:8px;">Infos utiles</h4>
                     <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin-bottom:26px;">Racontez-nous votre recette, ses ingrédients et l'histoire qui l'accompagne.</p>
                     <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-clock"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Attention</strong><span style="color:#fff;font-size:.87rem;">Une recette complète en un seul envoi.</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-phone-alt"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Besoin d'aide</strong><span style="color:#fff;font-size:.87rem;">+229 90 12 34 56</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-users"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Partage</strong><span style="color:#fff;font-size:.87rem;">Racontez votre recette et son histoire.</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-map-marker-alt"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Présence</strong><span style="color:#fff;font-size:.87rem;">Cotonou, Bénin</span></div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8" data-aos="fade-left">
                  <div class="fcard">
                     <div class="row g-3">
                        <div class="col-sm-6"><label class="flbl">Nom complet *</label><input type="text" class="fctrl" placeholder="Nom Prénom"/></div>
                        <div class="col-sm-6"><label class="flbl">Téléphone *</label><input type="tel" class="fctrl" placeholder="+229 90 12 34 56"/></div>
                        <div class="col-sm-6"><label class="flbl">Email *</label><input type="email" class="fctrl" placeholder="votre@email.com"/></div>
                        <div class="col-sm-6">
                           <label class="flbl">Type de recette *</label>
                           <select class="fctrl">
                              <option>Plats principaux</option>
                              <option>Soupes & sauces</option>
                              <option>Entrées</option>
                              <option>Boissons</option>
                              <option>Desserts</option>
                           </select>
                        </div>
                        <div class="col-sm-6"><label class="flbl">Nom de la recette</label><input type="text" class="fctrl" placeholder="Nom de la recette"/></div>
                        <div class="col-sm-6">
                           <label class="flbl">Niveau *</label>
                           <select class="fctrl">
                              <option>Facile</option>
                              <option>Intermédiaire</option>
                              <option>Difficile</option>
                           </select>
                        </div>
                        <div class="col-12"><label class="flbl">Détails</label><textarea class="fctrl" rows="3" placeholder="Ingrédients, préparation, astuces..."></textarea></div>
                        <div class="col-12"><button class="btn-red w-100 justify-content-center" id="resBtn"><i class="fas fa-calendar-check"></i>Soumettre la recette</button></div>
                     </div>
                     <div class="sucmsg" id="resOk">
                        <i class="fas fa-check-circle"></i>
                        <p>Recette soumise ! Nous la mettrons en ligne après vérification.</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
      <!-- BLOG -->
      <section id="blog">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Actualités</span>
               <h2 class="stitle">Nos dernières <span>Recettes</span></h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4">
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/blog/sauce_graine.jpeg" alt=""/>
                        <div class="bldatebdg"><span class="bd">14</span><span class="bm">Mar</span></div>
                     </div>
                     <div class="blbody">
                        <div class="bltag">Cuisine béninoise</div>
                        <div class="bltit"><a href="#">Comment réussir une bonne sauce graine à la maison</a></div>
                        <div class="blmeta"><span><i class="fas fa-user"></i>Equipe GoûtsBénin</span><span><i class="fas fa-comment"></i>24 commentaires</span></div>
                        <a href="#" class="blmore">Lire la suite <i class="fas fa-arrow-right"></i></a>
                     </div>
                  </div>
               </div>
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/blog/aloko.jpeg" alt=""/>
                        <div class="bldatebdg"><span class="bd">28</span><span class="bm">Fév</span></div>
                     </div>
                     <div class="blbody">
                        <div class="bltag">Tradition</div>
                        <div class="bltit"><a href="#">Le secret des bananes plantain frites bien dorées</a></div>
                        <div class="blmeta"><span><i class="fas fa-user"></i>Equipe GoûtsBénin</span><span><i class="fas fa-comment"></i>18 commentaires</span></div>
                        <a href="#" class="blmore">Lire la suite <i class="fas fa-arrow-right"></i></a>
                     </div>
                  </div>
               </div>
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="160">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/blog/abaara.jpeg" alt=""/>
                        <div class="bldatebdg"><span class="bd">05</span><span class="bm">Jan</span></div>
                     </div>
                     <div class="blbody">
                        <div class="bltag">Recettes</div>
                        <div class="bltit"><a href="#">Abará : la recette béninoise de l'entrée parfaite</a></div>
                        <div class="blmeta"><span><i class="fas fa-user"></i>Equipe GoûtsBénin</span><span><i class="fas fa-comment"></i>32 commentaires</span></div>
                        <a href="#" class="blmore">Lire la suite <i class="fas fa-arrow-right"></i></a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
      <!-- NEWSLETTER -->
      <section id="newsletter">
         <div class="nlbg"></div>
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
	  
      <!-- ============================================================
         FIX 6 – CONTACT FORM
         ============================================================ -->
      <section id="contact-section">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Contact</span>
               <h2 class="stitle">Contactez <span>GoûtsBénin</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">Une question, un commentaire ou une envie de partage de recette ? Écrivez-nous, nous y répondrons rapidement.</p>
            </div>
            <div class="row g-4">
               <div class="col-lg-4" data-aos="fade-right">
                  <div class="ctdark">
                     <h4>Restons en contact</h4>
                     <p class="ctsub">Nous répondons généralement sous 2 heures pendant les heures ouvrables.</p>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="ctinfo"><strong>Adresse</strong><span>Cotonou, Bénin</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-phone-alt"></i></div>
                        <div class="ctinfo"><strong>Téléphone</strong><span>+229 90 12 34 56</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-envelope"></i></div>
                        <div class="ctinfo"><strong>Email</strong><span>contact@goutsbenin.org</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-clock"></i></div>
                        <div class="ctinfo"><strong>Horaires</strong><span>Mer - Sam: 09:00 - 19:00</span></div>
                     </div>
                     <div class="ctsocrow">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8" data-aos="fade-left">
                  <div class="fcard">
                     <div class="row g-3">
                        <div class="col-sm-6"><label class="flbl">Nom complet *</label><input type="text" class="fctrl" placeholder="Nom Prénom"/></div>
                        <div class="col-sm-6"><label class="flbl">Email *</label><input type="email" class="fctrl" placeholder="votre@email.com"/></div>
                        <div class="col-sm-6"><label class="flbl">Téléphone</label><input type="tel" class="fctrl" placeholder="+229 90 12 34 56"/></div>
                        <div class="col-sm-6">
                           <label class="flbl">Sujet *</label>
                           <select class="fctrl">
                              <option>Suggestion de recette</option>
                              <option>Question</option>
                              <option>Commentaire</option>
                              <option>Partenariat</option>
                              <option>Autre</option>
                           </select>
                        </div>
                        <div class="col-12"><label class="flbl">Message *</label><textarea class="fctrl" rows="5" placeholder="Écrivez votre message ici..."></textarea></div>
                        <div class="col-12"><button class="btn-red" id="ctcBtn"><i class="fas fa-paper-plane"></i>Envoyer</button></div>
                     </div>
                     <div class="sucmsg" id="ctcOk">
                        <i class="fas fa-check-circle"></i>
                        <p>Message envoyé ! Nous vous répondrons bientôt.</p>
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
      <!-- Filtre recettes -->
      <script>
      (function () {
         const btns  = document.querySelectorAll('.mf-btn');
         const items = document.querySelectorAll('.menu-item');

         btns.forEach(function(btn) {
            btn.addEventListener('click', function() {
               // Activer le bouton cliqué
               btns.forEach(function(b) { b.classList.remove('active'); });
               btn.classList.add('active');

               var filter = btn.dataset.filter;

               items.forEach(function(item) {
                  if (filter === 'all' || item.dataset.cat === filter) {
                     item.classList.remove('hidden');
                  } else {
                     item.classList.add('hidden');
                  }
               });
            });
         });
      })();
      </script>
   </body>
</html>
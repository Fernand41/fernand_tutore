<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
$pdo = Database::getInstance();

$slug = trim($_GET['slug'] ?? '');
$id   = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($slug === '' && $id === null) {
    header('Location: recettes.php');
    exit;
}

$sql = 'SELECT r.*, c.nom AS categorie, c.slug AS categorie_slug, u.pseudo AS auteur, u.id AS auteur_id
        FROM recettes r
        JOIN categories_recettes c ON r.id_categorie = c.id
        JOIN utilisateurs u ON r.id_auteur = u.id
        WHERE r.statut = \'publie\'';
$params = [];
if ($id !== null && $id > 0) {
    $sql .= ' AND r.id = ?';
    $params[] = $id;
} else {
    $sql .= ' AND r.slug = ?';
    $params[] = $slug;
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipe = $stmt->fetch();
if (!$recipe) {
    header('Location: recettes.php');
    exit;
}

$loggedIn = isLoggedIn();
$alreadyFavorited = false;
$comments = [];
$commentsCount = 0;
$suggestions = [];
$embedVideoUrl = null;

function parseList(string $text): array {
    return array_filter(array_map('trim', preg_split('/[\r\n]+/', $text)), fn($item) => $item !== '');
}

function getYouTubeEmbedUrl(string $url): ?string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return null;
}

try {
    if ($loggedIn) {
        $favStmt = $pdo->prepare('SELECT id FROM favoris WHERE id_utilisateur = ? AND id_recette = ? LIMIT 1');
        $favStmt->execute([currentUserId(), $recipe['id']]);
        $alreadyFavorited = (bool) $favStmt->fetch();
    }

    $commentStmt = $pdo->prepare(
        'SELECT c.*, COALESCE(u.pseudo, "Utilisateur") AS auteur_nom
         FROM commentaires c
         LEFT JOIN utilisateurs u ON u.id = c.id_utilisateur
         WHERE c.id_recette = ? AND c.statut = \'approuve\'
         ORDER BY c.date_creation DESC'
    );
    $commentStmt->execute([$recipe['id']]);
    $comments = $commentStmt->fetchAll();
    $commentsCount = count($comments);

    $suggestStmt = $pdo->prepare(
        'SELECT r.id, r.slug, r.titre, r.image, r.note_moyenne
         FROM recettes r
         WHERE r.statut = \'publie\' AND r.id != ? AND r.id_categorie = ?
         ORDER BY r.note_moyenne DESC, r.date_creation DESC
         LIMIT 4'
    );
    $suggestStmt->execute([$recipe['id'], $recipe['id_categorie']]);
    $suggestions = $suggestStmt->fetchAll();
} catch (PDOException $e) {
    error_log('[recette] Erreur de chargement des données : ' . $e->getMessage());
}

$embedVideoUrl = !empty($recipe['video_youtube']) ? getYouTubeEmbedUrl($recipe['video_youtube']) : null;
$ingredients = parseList($recipe['ingredients'] ?? '');
$steps = parseList($recipe['etapes'] ?? '');
$recipeImage = $recipe['image'] ? '../uploads/recettes/' . $recipe['image'] : 'img/menu/1.jpg';
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="GoûtsBénin">
      <meta name="description" content="Découvrez cette délicieuse recette traditionnelle béninoise sur Goûts du Bénin.">
      <title>Détail de la Recette - GoûtsBénin</title>
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
         .recipe-header {
            position: relative;
            padding: 140px 0 80px 0;
            background: linear-gradient(180deg, rgba(21, 21, 21, 0.9) 0%, rgba(21, 21, 21, 0.95) 100%);
            color: #fff;
            overflow: hidden;
         }
         .recipe-header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            filter: blur(10px) brightness(0.3);
            transform: scale(1.1);
            z-index: 1;
         }
         .recipe-header .container {
            position: relative;
            z-index: 2;
         }
         .recipe-main-img {
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 4px solid rgba(255,255,255,0.05);
            max-height: 450px;
            width: 100%;
            object-fit: cover;
         }
         .meta-item {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
         }
         .meta-item:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.1);
            border-color: var(--secondary);
         }
         .meta-item i {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-bottom: 8px;
            display: block;
         }
         .meta-item span {
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
         }
         .meta-item strong {
            font-size: 1.1rem;
            color: #fff;
         }
         .recipe-body {
            background: var(--bg-color);
            padding: 80px 0;
         }
         .section-card {
            background: #fff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 30px;
         }
         .ingredients-list {
            list-style: none;
            padding-left: 0;
         }
         .ingredients-list li {
            position: relative;
            padding-left: 35px;
            margin-bottom: 15px;
            font-size: 1.05rem;
            color: #444;
            cursor: pointer;
            user-select: none;
         }
         .ingredients-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 3px;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 4px;
            transition: all 0.2s ease;
         }
         .ingredients-list li.checked {
            text-decoration: line-through;
            color: #888;
         }
         .ingredients-list li.checked::before {
            background: var(--secondary);
            border-color: var(--secondary);
         }
         .ingredients-list li.checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 3px;
            top: 4px;
            font-size: 12px;
            color: #fff;
         }
         .step-item {
            position: relative;
            padding-left: 60px;
            margin-bottom: 35px;
         }
         .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(230, 92, 92, 0.4);
         }
         .step-content h5 {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 8px;
         }
         .step-content p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 0;
         }
         .step-item:last-child {
            margin-bottom: 0;
         }
         .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
         }
         .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
         }
         .comment-item {
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 20px 0;
         }
         .comment-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
         }
         .comment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
         }
         .rating-stars-input {
            font-size: 1.6rem;
            cursor: pointer;
            display: inline-block;
         }
         .rating-stars-input i {
            color: #ccc;
            transition: color 0.2s ease;
         }
         .rating-stars-input i.active,
         .rating-stars-input i:hover,
         .rating-stars-input i:hover ~ i {
            color: var(--secondary);
         }
         .sidebar-widget {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 30px;
         }
         .widget-title {
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 12px;
            margin-bottom: 20px;
            position: relative;
         }
         .suggested-recipe-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
         }
         .suggested-recipe-item:last-child {
            margin-bottom: 0;
         }
         .suggested-recipe-item:hover {
            transform: translateX(5px);
         }
         .suggested-recipe-img {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid rgba(0,0,0,0.1);
         }
         .suggested-recipe-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
            text-decoration: none;
            display: block;
         }
         .suggested-recipe-title:hover {
            color: var(--primary);
         }
         .suggested-recipe-stars {
            font-size: 0.75rem;
         }
         .fav-btn-large {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 2px solid var(--primary);
            background: transparent;
            color: var(--primary);
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
         }
         .fav-btn-large.active {
            background: var(--primary);
            color: #fff;
         }
         .fav-btn-large:hover {
            background: var(--primary);
            color: #fff;
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
         RECIPE DETAIL HEADER
         ============================================================ -->
      <section class="recipe-header">
         <div class="recipe-header-bg" id="headerBgImage" style="background-image: url('<?= e($recipeImage) ?>');"></div>
         <div class="container">
            <div class="row align-items-center g-5">
               <div class="col-lg-7" data-aos="fade-right">
                  <div class="mb-3">
                     <span class="badge bg-danger px-3 py-2 text-uppercase mb-2" style="letter-spacing: 1px;"><?= e($recipe['categorie']) ?></span>
                     <span class="badge bg-secondary px-3 py-2 text-uppercase mb-2 ms-2"><?= e(ucfirst($recipe['difficulte'] ?? '')) ?></span>
                  </div>
                  <h1 class="display-4 fw-bold mb-3 text-white"><?= e($recipe['titre']) ?></h1>
                  <p class="lead text-white-50 mb-4"><?= nl2br(e($recipe['description'])) ?></p>
                  
                  <div class="row g-3">
                     <div class="col-6 col-sm-3">
                        <div class="meta-item">
                           <i class="far fa-clock"></i>
                           <span>Préparation</span>
                           <strong><?= (int) ($recipe['temps_prep'] ?? 0) ?> min</strong>
                        </div>
                     </div>
                     <div class="col-6 col-sm-3">
                        <div class="meta-item">
                           <i class="fas fa-fire"></i>
                           <span>Cuisson</span>
                           <strong><?= (int) ($recipe['temps_cuisson'] ?? 0) ?> min</strong>
                        </div>
                     </div>
                     <div class="col-6 col-sm-3">
                        <div class="meta-item">
                           <i class="fas fa-users"></i>
                           <span>Portions</span>
                           <strong><?= (int) ($recipe['nb_personnes'] ?? 0) ?> pers.</strong>
                        </div>
                     </div>
                     <div class="col-6 col-sm-3">
                        <div class="meta-item">
                           <i class="fas fa-star text-warning"></i>
                           <span>Note</span>
                           <strong><?= round((float) ($recipe['note_moyenne'] ?? 0), 1) ?> / 5</strong>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-5" data-aos="fade-left">
                  <img src="<?= e($recipeImage) ?>" class="recipe-main-img" alt="<?= e($recipe['titre']) ?>"/>
               </div>
            </div>
         </div>
      </section>

      <!-- ============================================================
         RECIPE CONTENT & SIDEBAR
         ============================================================ -->
      <section class="recipe-body">
         <div class="container">
            <div class="row g-5">
               <!-- Main Content -->
               <div class="col-lg-8" data-aos="fade-up">
                  <!-- Ingredients -->
                  <div class="section-card">
                     <h3 class="fw-bold mb-4" style="color:var(--dark);"><i class="fas fa-utensils text-danger me-2"></i>Ingrédients</h3>
                     <p class="text-muted mb-4" style="font-size: 0.9rem;"><i class="fas fa-info-circle me-1"></i> Astuce : vous pouvez cocher les ingrédients que vous avez déjà réunis.</p>
                     <ul class="ingredients-list" id="ingredientsList">
                        <?php if (!empty($ingredients)): ?>
                           <?php foreach ($ingredients as $ingredient): ?>
                              <li><?= e($ingredient) ?></li>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <li>Aucun ingrédient renseigné pour cette recette.</li>
                        <?php endif; ?>
                     </ul>
                  </div>

                  <!-- Steps -->
                  <div class="section-card">
                     <h3 class="fw-bold mb-4" style="color:var(--dark);"><i class="fas fa-list-ol text-danger me-2"></i>Étapes de préparation</h3>
                     <div id="stepsContainer">
                        <?php if (!empty($steps)): ?>
                           <?php foreach ($steps as $index => $step): ?>
                              <div class="step-item">
                                 <div class="step-number"><?= $index + 1 ?></div>
                                 <div class="step-content">
                                    <h5>Étape <?= $index + 1 ?></h5>
                                    <p><?= e($step) ?></p>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <p class="text-muted">Aucune étape de préparation disponible.</p>
                        <?php endif; ?>
                     </div>
                  </div>

                  <!-- Video (YouTube) -->
                  <?php if (!empty($embedVideoUrl)): ?>
                     <div class="section-card" id="videoSection">
                        <h3 class="fw-bold mb-4" style="color:var(--dark);"><i class="fab fa-youtube text-danger me-2"></i>Tutoriel Vidéo</h3>
                        <div class="video-container">
                           <iframe id="videoIframe" src="<?= e($embedVideoUrl) ?>" allowfullscreen></iframe>
                        </div>
                     </div>
                  <?php endif; ?>

                  <!-- Comments & Ratings -->
                  <div class="section-card">
                     <h3 class="fw-bold mb-4" style="color:var(--dark);"><i class="far fa-comments text-danger me-2"></i>Commentaires (<span id="commentsCount"><?= (int) $commentsCount ?></span>)</h3>
                     <!-- CHECK_COMMENTS_QUERY -->
                     <!-- DEBUG_COMMENTS <?= json_encode($comments) ?> -->
                     <div id="commentsContainer">
                        <?php if (!empty($comments)): ?>
                           <?php foreach ($comments as $comment): ?>
                              <div class="comment-item">
                                 <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="comment-avatar"><?= strtoupper(substr(e($comment['auteur_nom'] ?? 'U'), 0, 1)) ?></div>
                                    <div>
                                       <h6 class="mb-1 fw-semibold"><?= e($comment['auteur_nom'] ?? 'Utilisateur') ?></h6>
                                       <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['date_creation'])) ?></small>
                                    </div>
                                 </div>
                                 <p class="mb-0"><?= nl2br(e($comment['contenu'])) ?></p>
                              </div>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <p class="text-muted">Aucun commentaire n'a encore été publié pour cette recette.</p>
                        <?php endif; ?>
                     </div>
                  </div>

                  <!-- Add Comment Form -->
                  <div class="section-card">
                     <h3 class="fw-bold mb-4" style="color:var(--dark);"><i class="fas fa-pen-nib text-danger me-2"></i>Laisser un avis</h3>
                     <?php if ($loggedIn): ?>
                        <form method="POST" action="../actions/recette_comment.php">
                           <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                           <input type="hidden" name="id_recette" value="<?= (int) $recipe['id'] ?>">
                           <div class="mb-4">
                              <label class="form-label fw-bold mb-2">Votre note de satisfaction</label>
                              <div class="rating-stars-input" id="ratingInput">
                                 <i class="far fa-star" data-val="1"></i>
                                 <i class="far fa-star" data-val="2"></i>
                                 <i class="far fa-star" data-val="3"></i>
                                 <i class="far fa-star" data-val="4"></i>
                                 <i class="far fa-star" data-val="5"></i>
                              </div>
                              <input type="hidden" name="note" id="selectedNote" value="0" required>
                           </div>
                           <div class="mb-4">
                              <label for="commentContent" class="form-label fw-bold">Votre commentaire</label>
                              <textarea class="form-control" id="commentContent" name="contenu" rows="5" placeholder="Que pensez-vous de cette recette ? Partagez votre expérience avec la communauté !" required></textarea>
                           </div>
                           <div id="submitMessage" class="mb-3"></div>
                           <button type="submit" class="btn btn-danger px-4 py-2" id="submitBtn"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
                        </form>
                     <?php else: ?>
                        <div class="alert alert-dark border-0 p-4 text-center">
                           <p class="mb-3">Vous devez être connecté pour donner une note et écrire un commentaire.</p>
                           <a href="login.php?redirect=<?= urlencode('recette.php?slug=' . $recipe['slug']) ?>" class="btn btn-danger px-4">Se connecter</a>
                        </div>
                     <?php endif; ?>
                  </div>
               </div>

               <!-- Sidebar -->
               <div class="col-lg-4">
                  <!-- Actions Widget -->
                  <div class="sidebar-widget text-center" data-aos="fade-up">
                     <h4 class="widget-title">Actions</h4>
                     <form method="POST" action="../actions/recette_favorite.php">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id_recette" value="<?= (int) $recipe['id'] ?>">
                        <button type="submit" class="fav-btn-large mb-3 <?= $alreadyFavorited ? 'active' : '' ?>">
                           <i class="<?= $alreadyFavorited ? 'fas' : 'far' ?> fa-heart"></i>
                           <span><?= $alreadyFavorited ? 'Retirer des favoris' : 'Ajouter aux Favoris' ?></span>
                        </button>
                     </form>
                     <div class="mt-4 pt-3 border-top">
                        <p class="small text-muted mb-2">Partager cette recette :</p>
                        <div class="d-flex justify-content-center gap-3">
                           <a href="#" class="btn btn-outline-dark btn-sm rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                           <a href="#" class="btn btn-outline-dark btn-sm rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;"><i class="fab fa-twitter"></i></a>
                           <a href="#" class="btn btn-outline-dark btn-sm rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;"><i class="fab fa-pinterest"></i></a>
                           <a href="#" class="btn btn-outline-dark btn-sm rounded-circle" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-envelope"></i></a>
                        </div>
                     </div>
                  </div>

                  <!-- Author Widget -->
                  <div class="sidebar-widget" data-aos="fade-up">
                     <h4 class="widget-title">Auteur</h4>
                     <div class="d-flex align-items-center gap-3">
                        <div class="comment-avatar"><?= strtoupper(substr(e($recipe['auteur'] ?? 'U'), 0, 1)) ?></div>
                        <div>
                           <h5 class="fw-bold mb-1"><?= e($recipe['auteur'] ?? 'Utilisateur') ?></h5>
                           <span class="badge bg-dark">Membre</span>
                        </div>
                     </div>
                     <p class="text-muted mt-3 small mb-0">Cette recette a été proposée par un membre passionné de la gastronomie béninoise.</p>
                  </div>

                  <!-- Suggestions Widget -->
                  <div class="sidebar-widget" data-aos="fade-up">
                     <h4 class="widget-title">Top Recettes</h4>
                     <div id="suggestionsContainer">
                        <?php if (!empty($suggestions)): ?>
                           <?php foreach ($suggestions as $suggestion): ?>
                              <?php $suggestImage = $suggestion['image'] ? '../uploads/recettes/' . $suggestion['image'] : 'img/menu/1.jpg'; ?>
                              <a href="recette.php?slug=<?= e($suggestion['slug']) ?>" class="suggested-recipe-item text-decoration-none">
                                 <img src="<?= e($suggestImage) ?>" class="suggested-recipe-img" alt="<?= e($suggestion['titre']) ?>">
                                 <div>
                                    <div class="suggested-recipe-title"><?= e($suggestion['titre']) ?></div>
                                    <div class="suggested-recipe-stars text-muted"><i class="fas fa-star text-warning"></i> <?= round((float) $suggestion['note_moyenne'], 1) ?> / 5</div>
                                 </div>
                              </a>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <p class="text-muted mb-0">Aucune suggestion disponible pour le moment.</p>
                        <?php endif; ?>
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
      <script>
         document.addEventListener('DOMContentLoaded', function() {
             const ratingStars = document.querySelectorAll('#ratingInput i');
             const noteInput = document.getElementById('selectedNote');
             if (ratingStars.length && noteInput) {
                 ratingStars.forEach(function(star) {
                     star.addEventListener('click', function() {
                         const value = Number(this.dataset.val || 0);
                         noteInput.value = value;
                         ratingStars.forEach(function(item) {
                             item.classList.toggle('active', Number(item.dataset.val) <= value);
                         });
                     });
                 });
             }
         });
      </script>
   </body>
</html>

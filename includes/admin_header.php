<?php
/**
 * includes/admin_header.php
 * En-tête + sidebar du BACK-END (AdminLTE) — 100 % PHP, zéro JS d'injection
 *
 * Variables attendues avant l'include :
 *   $adminPageTitle  — titre de la page (ex : "Recettes | Admin")
 *   $adminActivePage — page active pour surbrillance sidebar
 *                      valeurs : 'dashboard' | 'recettes' | 'commentaires' | 'favoris' | 'utilisateurs' | 'categories'
 *   $breadcrumb      — tableau [['label'=>'Recettes','url'=>'recettes.php'], ...]
 *                      Le dernier élément est automatiquement "active".
 */

require_once __DIR__ . '/session.php';

// Protection : seul un admin peut accéder
requireAdmin('../front_end/login.php');

$adminPageTitle  = $adminPageTitle  ?? 'Administration | Goûts du Bénin';
$adminActivePage = $adminActivePage ?? 'dashboard';
$breadcrumb      = $breadcrumb      ?? [];

// Chemin vers les assets AdminLTE (back_end/)
// Depuis admin/  → ../back_end/
$adminAssets = $adminAssets ?? '../back_end';

$userName = e(currentUserName() ?? 'Admin');
?>
<!doctype html>
<html lang="fr">
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
   <title><?= e($adminPageTitle) ?></title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
   <meta name="color-scheme" content="light dark"/>
   <meta name="theme-color" content="#198754" media="(prefers-color-scheme: light)"/>
   <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)"/>

   <!-- Fonts -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous"/>
   <!-- OverlayScrollbars -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous"/>
   <!-- Bootstrap Icons -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous"/>
   <!-- AdminLTE CSS -->
   <link rel="stylesheet" href="<?= $adminAssets ?>/css/adminlte.css"/>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
<div class="app-wrapper">

   <!--begin::Header-->
   <nav class="app-header navbar navbar-expand bg-body shadow-sm">
      <div class="container-fluid">

         <!--begin::Start Navbar Links-->
         <ul class="navbar-nav">
            <li class="nav-item">
               <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                  <i class="bi bi-list"></i>
               </a>
            </li>
            <li class="nav-item d-none d-md-block">
               <span class="navbar-text fw-bold text-success ps-2">Tableau de bord Administration</span>
            </li>
         </ul>
         <!--end::Start Navbar Links-->

         <!--begin::End Navbar Links-->
         <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown user-menu">
               <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                  <span class="user-image rounded-circle shadow bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:2rem;height:2rem;">
                     <?= strtoupper(substr($userName, 0, 1)) ?>
                  </span>
                  <span class="d-none d-md-inline ms-2"><?= $userName ?></span>
               </a>
               <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                  <li class="user-header text-bg-success d-flex align-items-center gap-3">
                     <span class="rounded-circle shadow bg-white text-success d-inline-flex align-items-center justify-content-center" style="width:3rem;height:3rem;font-weight:700;font-size:1.1rem;">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                     </span>
                     <div>
                        <p class="mb-0">
                           <?= $userName ?>
                        </p>
                        <small>Administrateur · Goûts du Bénin</small>
                     </div>
                  </li>
                  <li class="user-footer border-top border-secondary">
                     <a href="../pages/profil.php" class="btn btn-success btn-flat">Profil</a>
                     <a href="../actions/auth_logout.php" class="btn btn-danger btn-flat float-end">Déconnexion</a>
                  </li>
               </ul>
            </li>
         </ul>
         <!--end::End Navbar Links-->

      </div>
   </nav>
   <!--end::Header-->

   <!--begin::Sidebar-->
   <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
         <a href="index.php" class="brand-link">
            <img src="<?= $adminAssets ?>/assets/img/AdminLTELogo.png"
                 alt="Logo" class="brand-image opacity-75 shadow"/>
            <span class="brand-text fw-bold">Goûts du Bénin</span>
         </a>
      </div>
      <!--end::Sidebar Brand-->

      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">

         <!-- Info utilisateur -->
         <div class="user-panel mt-3 pb-3 mb-3 d-flex border-bottom border-secondary align-items-center px-3">
            <div class="image">
               <span class="img-circle elevation-2 bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:2.1rem;height:2.1rem;font-weight:700;"><?= strtoupper(substr($userName, 0, 1)) ?></span>
            </div>
            <div class="info ps-3">
               <span class="d-block text-white fw-bold"><?= $userName ?></span>
               <span class="badge bg-success text-white p-1" style="font-size:.7rem;">Administrateur</span>
            </div>
         </div>

         <!-- Menu de navigation -->
         <nav class="mt-2" aria-label="Navigation principale">
            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                role="menu">

               <li class="nav-header">MENU PRINCIPAL</li>

               <li class="nav-item">
                  <a href="index.php"
                     class="nav-link <?= $adminActivePage === 'dashboard' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-speedometer2"></i>
                     <p>Dashboard</p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="recettes.php"
                     class="nav-link <?= $adminActivePage === 'recettes' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-journal-text"></i>
                     <p>
                        Recettes
                        <?php
                        // Badge recettes en attente (optionnel — chargé si PDO disponible)
                        if (class_exists('Database')):
                           try {
                              $pdo = Database::getInstance();
                              $n = $pdo->query("SELECT COUNT(*) FROM recettes WHERE statut='en_attente'")->fetchColumn();
                              if ($n > 0): ?>
                           <span class="badge bg-warning text-dark ms-auto"><?= (int)$n ?></span>
                        <?php endif;
                           } catch(Exception $e) {}
                        endif; ?>
                     </p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="commentaires.php"
                     class="nav-link <?= $adminActivePage === 'commentaires' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-chat-left-text-fill"></i>
                     <p>Commentaires</p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="favoris.php"
                     class="nav-link <?= $adminActivePage === 'favoris' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-heart-fill"></i>
                     <p>Favoris & Notes</p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="utilisateurs.php"
                     class="nav-link <?= $adminActivePage === 'utilisateurs' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-people-fill"></i>
                     <p>Utilisateurs</p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="categories.php"
                     class="nav-link <?= $adminActivePage === 'categories' ? 'active' : '' ?>">
                     <i class="nav-icon bi bi-tags-fill"></i>
                     <p>Catégories</p>
                  </a>
               </li>

               <li class="nav-header border-top border-secondary mt-3 pt-2">SESSION</li>

               <li class="nav-item">
                  <a href="../front_end/index.php" class="nav-link" target="_blank">
                     <i class="nav-icon bi bi-house-door-fill"></i>
                     <p>Voir le site</p>
                  </a>
               </li>

               <li class="nav-item">
                  <a href="../actions/auth_logout.php" class="nav-link text-danger">
                     <i class="nav-icon bi bi-box-arrow-right"></i>
                     <p>Déconnexion</p>
                  </a>
               </li>

            </ul>
         </nav>
         <!--end::Sidebar Menu-->

      </div>
      <!--end::Sidebar Wrapper-->
   </aside>
   <!--end::Sidebar-->

   <!--begin::App Main-->
   <main class="app-main">

      <!--begin::App Content Header (breadcrumb + titre page)-->
      <div class="app-content-header">
         <div class="container-fluid">
            <div class="row">
               <div class="col-sm-6">
                  <h3 class="mb-0 text-success fw-bold">
                     <?= e($adminPageTitle) ?>
                  </h3>
               </div>
               <?php if (!empty($breadcrumb)): ?>
               <div class="col-sm-6">
                  <ol class="breadcrumb float-sm-end">
                     <li class="breadcrumb-item">
                        <a href="index.php">Accueil</a>
                     </li>
                     <?php foreach ($breadcrumb as $i => $crumb): ?>
                        <?php if ($i === array_key_last($breadcrumb)): ?>
                           <li class="breadcrumb-item active" aria-current="page">
                              <?= e($crumb['label']) ?>
                           </li>
                        <?php else: ?>
                           <li class="breadcrumb-item">
                              <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                           </li>
                        <?php endif; ?>
                     <?php endforeach; ?>
                  </ol>
               </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <!--end::App Content Header-->

      <!--begin::App Content-->
      <div class="app-content">
         <div class="container-fluid">

            <!-- Zone des messages flash -->
            <?php displayFlash(); ?>
<?php
require_once __DIR__ . '/../includes/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>GoûtsBénin - Inscription</title>
      <?php $registerRedirect = trim($_GET['redirect'] ?? ''); ?>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
      <link href="css/bootstrap.min.css" rel="stylesheet"/>
      <link rel="stylesheet" href="css/all.min.css"/>
      <link rel="stylesheet" href="css/style.css" />
      <style>
         body {
            background-color: var(--dark-light) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
         }
         .login-card {
            background: var(--dark);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 18px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
         }
         .blogo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
         }
         .bico {
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
         }
         .bname {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
         }
         .bname span {
            color: var(--primary);
         }
         .bsub {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.5px;
         }
         .fctrl {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
         }
         .fctrl:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(232, 40, 26, 0.15) !important;
         }
         .flbl {
            color: rgba(255,255,255,0.7) !important;
            font-size: 0.85rem !important;
            margin-bottom: 6px !important;
            font-weight: 500 !important;
         }
         .btn-red {
            border-radius: 10px !important;
            padding: 12px 20px !important;
            font-weight: 600 !important;
            width: 100%;
            justify-content: center;
         }
         .link-primary {
            color: var(--primary) !important;
            text-decoration: none;
         }
         .link-primary:hover {
            text-decoration: underline;
         }
         .invalid-feedback {
            color: #ff6b6b !important;
            font-size: 0.8rem;
         }
         .alert {
            background: rgba(255, 107, 107, 0.1) !important;
            border: 1px solid rgba(255, 107, 107, 0.2) !important;
            color: #ff8787 !important;
            border-radius: 10px;
         }
         .alert-success {
            background: rgba(74, 222, 128, 0.1) !important;
            border: 1px solid rgba(74, 222, 128, 0.2) !important;
            color: #4ade80 !important;
         }
         .back-home-btn {
            position: fixed;
            top: 20px;
            left: 24px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            border: 1px solid var(--primary);
            color: #fff;
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 4px 14px rgba(232, 40, 26, 0.35);
            transition: all 0.25s ease;
            z-index: 999;
         }
         .back-home-btn i {
            font-size: 0.8rem;
            transition: transform 0.25s ease;
         }
         .back-home-btn:hover {
            background: #c0180f;
            border-color: #c0180f;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(232, 40, 26, 0.45);
         }
         .back-home-btn:hover i {
            transform: translateX(-3px);
         }
      </style>
   </head>
   <body>
      <a href="index.php" class="back-home-btn">
         <i class="fas fa-arrow-left"></i>
         <span>Retour à l'accueil</span>
      </a>
      <div class="login-card">
         <div class="blogo">
            <div class="bico"><i class="fas fa-leaf"></i></div>
            <div>
               <div class="bname">Goûts<span>Bénin</span></div>
               <div class="bsub">Créer un Compte</div>
            </div>
         </div>
         
         <div id="alert-container"></div>
         <?php displayFlash(); ?>

         <form id="registerForm" action="../actions/auth_register.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($registerRedirect !== ''): ?>
               <input type="hidden" name="redirect" value="<?= e($registerRedirect) ?>">
            <?php endif; ?>
            <div class="mb-3">
               <label class="flbl">Pseudo / Nom d'utilisateur *</label>
               <input type="text" id="registerPseudo" name="pseudo" class="form-control fctrl" placeholder="Ex: Koffi97" required />
               <div class="invalid-feedback">Le pseudo est obligatoire.</div>
            </div>

            <div class="mb-3">
               <label class="flbl">Adresse email *</label>
               <input type="email" id="registerEmail" name="email" class="form-control fctrl" placeholder="exemple@domaine.bj" required />
               <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
            </div>
            
            <div class="mb-4">
               <label class="flbl">Mot de passe *</label>
               <input type="password" id="registerPassword" name="mot_de_passe" class="form-control fctrl" placeholder="Minimum 8 caractères" required minlength="8" />
               <div class="invalid-feedback">Le mot de passe doit contenir au moins 8 caractères.</div>
            </div>
            <div class="mb-4">
               <label class="flbl">Confirmez le mot de passe *</label>
               <input type="password" id="registerConfirmPassword" name="confirmation" class="form-control fctrl" placeholder="Confirmez votre mot de passe" required minlength="8" />
               <div class="invalid-feedback">Veuillez confirmer votre mot de passe.</div>
            </div>
            
            <button type="submit" id="registerBtn" class="btn-red mb-3 d-flex align-items-center gap-2">
               <span id="registerBtnContent"><i class="fas fa-user-plus"></i> S'inscrire</span>
               <span id="registerSpinner" style="display:none; align-items:center;">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                  Inscription en cours...
               </span>
            </button>
            
            <div class="text-center text-white-50" style="font-size: 0.85rem;">
               Déjà un compte ? <a href="login.php<?= $registerRedirect !== '' ? '?redirect=' . urlencode($registerRedirect) : '' ?>" class="link-primary form-link-loader">Se connecter</a>
            </div>
         </form>
      </div>

      <script src="js/jquery-3.7.1.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/main.js"></script>
   </body>
</html>
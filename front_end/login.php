<?php
require_once __DIR__ . '/../includes/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>GoûtsBénin - Connexion Administration</title>
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
      </style>
   </head>
   <body>
      <div class="login-card">
         <div class="blogo">
            <div class="bico"><i class="fas fa-leaf"></i></div>
            <div>
               <div class="bname">Goûts<span>Bénin</span></div>
               <div class="bsub">Espace Connexion</div>
            </div>
         </div>
         
         <div id="alert-container"></div>
         <?php displayFlash(); ?>

         <form id="loginForm" action="../actions/auth_login.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
               <label class="flbl">Adresse email *</label>
               <input type="email" id="loginEmail" name="email" class="form-control fctrl" placeholder="exemple@domaine.bj" required />
               <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
            </div>
            
            <div class="mb-4">
               <label class="flbl">Mot de passe *</label>
               <input type="password" id="loginPassword" name="mot_de_passe" class="form-control fctrl" placeholder="••••••••" required />
               <div class="invalid-feedback">Veuillez entrer votre mot de passe.</div>
            </div>

            <div class="form-check text-white mb-3">
               <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
               <label class="form-check-label" for="rememberMe">
                  Se souvenir de moi
               </label>
            </div>
            
            <button type="submit" class="btn-red mb-3 d-flex align-items-center gap-2">
               <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
            
            <div class="text-center text-white-50" style="font-size: 0.85rem;">
               Pas encore de compte ? <a href="inscription.php" class="link-primary">Créer un compte</a>
            </div>
         </form>
      </div>

   </body>
</html>

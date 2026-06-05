<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>GoûtsBénin - Inscription</title>
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
      </style>
   </head>
   <body>
      <div class="login-card">
         <div class="blogo">
            <div class="bico"><i class="fas fa-leaf"></i></div>
            <div>
               <div class="bname">Goûts<span>Bénin</span></div>
               <div class="bsub">Créer un Compte</div>
            </div>
         </div>
         
         <div id="alert-container"></div>

         <form id="registerForm" novalidate>
            <div class="mb-3">
               <label class="flbl">Pseudo / Nom d'utilisateur *</label>
               <input type="text" id="registerPseudo" class="form-control fctrl" placeholder="Ex: Koffi97" required />
               <div class="invalid-feedback">Le pseudo est obligatoire.</div>
            </div>

            <div class="mb-3">
               <label class="flbl">Adresse email *</label>
               <input type="email" id="registerEmail" class="form-control fctrl" placeholder="exemple@domaine.bj" required />
               <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
            </div>
            
            <div class="mb-4">
               <label class="flbl">Mot de passe *</label>
               <input type="password" id="registerPassword" class="form-control fctrl" placeholder="Minimum 6 caractères" required minlength="6" />
               <div class="invalid-feedback">Le mot de passe doit contenir au moins 6 caractères.</div>
            </div>
            
            <button type="submit" class="btn-red mb-3 d-flex align-items-center gap-2">
               <i class="fas fa-user-plus"></i> S'inscrire
            </button>
            
            <div class="text-center text-white-50" style="font-size: 0.85rem;">
               Déjà un compte ? <a href="login.php" class="link-primary">Se connecter</a>
            </div>
         </form>
      </div>

      <!-- Scripts JS -->
      <script src="js/jquery-3.7.1.min.js"></script>
      <script src="js/api.js"></script>
      <script src="js/auth.js"></script>
      
      <script>
         // Rediriger vers l'accueil si déjà connecté
         if (localStorage.getItem('jwt_token')) {
             window.location.href = 'index.php';
         }

         document.getElementById('registerForm').addEventListener('submit', async (e) => {
             e.preventDefault();
             const form = e.target;
             const pseudoInput = document.getElementById('registerPseudo');
             const emailInput = document.getElementById('registerEmail');
             const passwordInput = document.getElementById('registerPassword');
             const alertContainer = document.getElementById('alert-container');
             
             // Réinitialiser les validations
             form.classList.remove('was-validated');
             pseudoInput.classList.remove('is-invalid');
             emailInput.classList.remove('is-invalid');
             passwordInput.classList.remove('is-invalid');
             alertContainer.innerHTML = '';

             let isValid = true;

             if (!pseudoInput.value.trim()) {
                 pseudoInput.classList.add('is-invalid');
                 isValid = false;
             }

             if (!emailInput.value.trim() || !emailInput.value.includes('@')) {
                 emailInput.classList.add('is-invalid');
                 isValid = false;
             }

             if (!passwordInput.value || passwordInput.value.length < 6) {
                 passwordInput.classList.add('is-invalid');
                 isValid = false;
             }

             if (!isValid) return;

             try {
                 // Requête d'inscription via apiRequest
                 // Le backend attend 'nom', 'email', 'mot_de_passe'
                 await apiRequest('POST', '/auth/inscription', {
                     nom: pseudoInput.value.trim(),
                     email: emailInput.value.trim(),
                     mot_de_passe: passwordInput.value
                 });

                 // Succès de l'inscription
                 alertContainer.innerHTML = `
                     <div class="alert alert-success p-3 d-flex align-items-center gap-2 mb-3">
                         <i class="fas fa-check-circle text-success"></i>
                         <div>Inscription réussie ! Redirection vers la page de connexion...</div>
                     </div>
                 `;

                 // Rediriger après 2 secondes vers login.php
                 setTimeout(() => {
                     window.location.href = 'login.php';
                 }, 2000);

             } catch (error) {
                 alertContainer.innerHTML = `
                     <div class="alert p-3 d-flex align-items-center gap-2 mb-3">
                         <i class="fas fa-exclamation-circle text-danger"></i>
                         <div>${error.message || "Erreur lors de l'inscription."}</div>
                     </div>
                 `;
             }
         });
      </script>
   </body>
</html>

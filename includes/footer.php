<?php
/**
 * includes/footer.php
 * Pied de page commun du FRONT-END + chargement des scripts JS
 *
 * Variables attendues (optionnelles) :
 *   $assetsPath  — chemin vers front_assets (même que dans header.php)
 */

$assetsPath = $assetsPath ?? '../front_assets';
?>

   <!-- ============================================================
      FOOTER
   ============================================================ -->
   <footer>
      <div class="container">
         <div class="row g-5">

            <!-- Colonne 1 : Logo + description -->
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

            <!-- Colonne 2 : Liens rapides -->
            <div class="col-sm-6 col-lg-2">
               <div class="ftit">Liens rapides</div>
               <ul class="flinks ps-0">
                  <li><a href="index.php"><i class="fas fa-chevron-right"></i>Accueil</a></li>
                  <li><a href="index.php#about"><i class="fas fa-chevron-right"></i>À propos</a></li>
                  <li><a href="recettes.php"><i class="fas fa-chevron-right"></i>Recettes</a></li>
                  <li><a href="soumettre.php"><i class="fas fa-chevron-right"></i>Partager</a></li>
                  <li><a href="index.php#contact-section"><i class="fas fa-chevron-right"></i>Contact</a></li>
               </ul>
            </div>

            <!-- Colonne 3 : Catégories -->
            <div class="col-sm-6 col-lg-2">
               <div class="ftit">Catégories</div>
               <ul class="flinks ps-0">
                  <li><a href="recettes.php?categorie=plats-principaux"><i class="fas fa-chevron-right"></i>Plats principaux</a></li>
                  <li><a href="recettes.php?categorie=soupes"><i class="fas fa-chevron-right"></i>Soupes</a></li>
                  <li><a href="recettes.php?categorie=entrees"><i class="fas fa-chevron-right"></i>Entrées</a></li>
                  <li><a href="recettes.php?categorie=boissons"><i class="fas fa-chevron-right"></i>Boissons</a></li>
                  <li><a href="recettes.php?categorie=desserts"><i class="fas fa-chevron-right"></i>Desserts</a></li>
               </ul>
            </div>

            <!-- Colonne 4 : Contact -->
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
                  <div class="fciinfo"><strong>Horaires</strong>Mer - Sam : 09:00 – 19:00</div>
               </div>
            </div>

         </div><!-- /.row -->
      </div><!-- /.container -->

      <!-- Barre de copyright -->
      <div class="fbot">
         <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
               <p>&copy; <?= date('Y') ?> <span>GoûtsBénin</span>. Tous droits réservés.</p>
               <div>
                  <a href="#">Confidentialité</a>
                  <a href="#">Conditions</a>
                  <a href="#">Cookies</a>
               </div>
            </div>
         </div>
      </div>
   </footer>

   <!-- Bouton retour en haut -->
   <button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})">
      <i class="fas fa-chevron-up"></i>
   </button>

   <!-- ============================================================
      SCRIPTS JS
   ============================================================ -->
   <script src="<?= $assetsPath ?>/js/jquery-3.7.1.min.js"></script>
   <script src="<?= $assetsPath ?>/js/bootstrap.bundle.min.js"></script>
   <script src="<?= $assetsPath ?>/js/aos.js"></script>
   <script src="<?= $assetsPath ?>/js/swiper-bundle.min.js"></script>
   <script src="<?= $assetsPath ?>/js/jquery.magnific-popup.min.js"></script>
   <script src="<?= $assetsPath ?>/js/main.js"></script>

</body>
</html>
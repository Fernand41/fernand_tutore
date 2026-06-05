<?php
/**
 * includes/admin_footer.php
 * Pied de page du BACK-END : fermeture des balises + scripts AdminLTE
 *
 * Variables attendues (optionnelles) :
 *   $adminAssets  — chemin vers les assets back_end/ (défaut : ../back_end)
 *   $extraScripts — tableau de chemins JS supplémentaires à charger sur cette page
 *                   ex : $extraScripts = ['../back_end/js/chart.js']
 */

$adminAssets  = $adminAssets  ?? '../back_end';
$extraScripts = $extraScripts ?? [];
?>

         </div><!-- /.container-fluid -->
      </div><!-- /.app-content -->

   </main>
   <!--end::App Main-->

   <!--begin::Footer-->
   <footer class="app-footer text-center">
      <strong>
         Copyright &copy; <?= date('Y') ?>
         <a href="../pages/index.php" class="text-success text-decoration-none">Goûts du Bénin</a>.
      </strong>
      Tous droits réservés.
   </footer>
   <!--end::Footer-->

</div>
<!--end::App Wrapper-->

<!--begin::Scripts-->
<!-- OverlayScrollbars -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!-- AdminLTE -->
<script src="<?= $adminAssets ?>/js/adminlte.js"></script>

<?php foreach ($extraScripts as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
<!--end::Scripts-->

</body>
</html>
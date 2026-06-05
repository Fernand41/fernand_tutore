<?php
/**
 * admin/index.php
 * Dashboard principal — statistiques globales du site
 */

require_once '../config/database.php';

$adminPageTitle  = 'Dashboard | Goûts du Bénin';
$adminActivePage = 'dashboard';
$breadcrumb      = [['label' => 'Dashboard', 'url' => 'index.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

$pdo = Database::getInstance();

// ── Statistiques globales ────────────────────────────────────────────────────
$nbRecettes        = (int) $pdo->query("SELECT COUNT(*) FROM recettes")->fetchColumn();
$nbRecettesAttente = (int) $pdo->query("SELECT COUNT(*) FROM recettes WHERE statut = 'en_attente'")->fetchColumn();
$nbUsers           = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nbCommentaires    = (int) $pdo->query("SELECT COUNT(*) FROM commentaires WHERE statut = 'en_attente'")->fetchColumn();
$nbCategories      = (int) $pdo->query("SELECT COUNT(*) FROM categories_recettes")->fetchColumn();

// ── 5 dernières recettes soumises ────────────────────────────────────────────
$dernieresRecettes = $pdo->query("
    SELECT r.id, r.titre, r.statut, r.date_publication, u.pseudo
    FROM recettes r
    JOIN utilisateurs u ON r.id_auteur = u.id
    ORDER BY r.date_publication DESC
    LIMIT 5
")->fetchAll();

// ── 5 derniers commentaires en attente ───────────────────────────────────────
$derniersCommentaires = $pdo->query("
    SELECT c.id, c.contenu, c.date, c.statut, u.pseudo, r.titre AS titre_recette
    FROM commentaires c
    JOIN utilisateurs u ON c.id_utilisateur = u.id
    JOIN recettes r     ON c.id_recette     = r.id
    WHERE c.statut = 'en_attente'
    ORDER BY c.date DESC
    LIMIT 5
")->fetchAll();

// ── 5 derniers inscrits ───────────────────────────────────────────────────────
$derniersUsers = $pdo->query("
    SELECT id, pseudo, email, role, date_creation
    FROM utilisateurs
    ORDER BY date_creation DESC
    LIMIT 5
")->fetchAll();

// ── Moyenne des notes ─────────────────────────────────────────────────────────
$moyenneNotes = (float) $pdo->query("SELECT COALESCE(ROUND(AVG(valeur),1), 0) FROM notes")->fetchColumn();
?>

<!-- ── Cartes statistiques ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

   <!-- Recettes publiées -->
   <div class="col-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success bg-opacity-10 p-3">
               <i class="bi bi-journal-richtext fs-3 text-success"></i>
            </div>
            <div>
               <div class="fs-2 fw-bold text-success"><?= $nbRecettes ?></div>
               <div class="text-muted small">Recettes publiées</div>
               <?php if ($nbRecettesAttente > 0): ?>
                  <span class="badge bg-warning text-dark"><?= $nbRecettesAttente ?> en attente</span>
               <?php endif; ?>
            </div>
         </div>
         <div class="card-footer bg-transparent border-0 pt-0">
            <a href="recettes.php" class="btn btn-sm btn-outline-success w-100">Gérer les recettes</a>
         </div>
      </div>
   </div>

   <!-- Utilisateurs -->
   <div class="col-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
               <i class="bi bi-people-fill fs-3 text-primary"></i>
            </div>
            <div>
               <div class="fs-2 fw-bold text-primary"><?= $nbUsers ?></div>
               <div class="text-muted small">Utilisateurs inscrits</div>
            </div>
         </div>
         <div class="card-footer bg-transparent border-0 pt-0">
            <a href="utilisateurs.php" class="btn btn-sm btn-outline-primary w-100">Gérer les utilisateurs</a>
         </div>
      </div>
   </div>

   <!-- Commentaires en attente -->
   <div class="col-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
               <i class="bi bi-chat-left-dots-fill fs-3 text-warning"></i>
            </div>
            <div>
               <div class="fs-2 fw-bold text-warning"><?= $nbCommentaires ?></div>
               <div class="text-muted small">Commentaires à modérer</div>
            </div>
         </div>
         <div class="card-footer bg-transparent border-0 pt-0">
            <a href="commentaires.php" class="btn btn-sm btn-outline-warning w-100">Modérer</a>
         </div>
      </div>
   </div>

   <!-- Catégories + Note moyenne -->
   <div class="col-6 col-xl-3">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
               <i class="bi bi-tags-fill fs-3 text-danger"></i>
            </div>
            <div>
               <div class="fs-2 fw-bold text-danger"><?= $nbCategories ?></div>
               <div class="text-muted small">Catégories culinaires</div>
               <div class="text-muted small">
                  <i class="bi bi-star-fill text-warning"></i>
                  Note moy. : <strong><?= $moyenneNotes ?>/5</strong>
               </div>
            </div>
         </div>
         <div class="card-footer bg-transparent border-0 pt-0">
            <a href="categories.php" class="btn btn-sm btn-outline-danger w-100">Gérer les catégories</a>
         </div>
      </div>
   </div>

</div>

<!-- ── Ligne 2 : Dernières recettes + Derniers commentaires ───────────────── -->
<div class="row g-3 mb-4">

   <!-- Dernières recettes soumises -->
   <div class="col-12 col-xl-7">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-success"></i>Dernières recettes soumises</h6>
            <a href="recettes.php" class="btn btn-sm btn-outline-success">Voir tout</a>
         </div>
         <div class="card-body p-0">
            <?php if (empty($dernieresRecettes)): ?>
               <p class="text-muted text-center py-4">Aucune recette pour l'instant.</p>
            <?php else: ?>
            <div class="table-responsive">
               <table class="table table-hover mb-0">
                  <thead class="table-light">
                     <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Statut</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($dernieresRecettes as $r): ?>
                     <tr>
                        <td>
                           <a href="recettes.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark fw-semibold">
                              <?= e($r['titre']) ?>
                           </a>
                        </td>
                        <td><?= e($r['pseudo']) ?></td>
                        <td class="text-muted small">
                           <?= date('d/m/Y', strtotime($r['date_publication'])) ?>
                        </td>
                        <td>
                           <?php
                              $badgeClass = match($r['statut']) {
                                 'publie'     => 'bg-success',
                                 'en_attente' => 'bg-warning text-dark',
                                 'rejete'     => 'bg-danger',
                                 default      => 'bg-secondary',
                              };
                              $badgeLabel = match($r['statut']) {
                                 'publie'     => 'Publié',
                                 'en_attente' => 'En attente',
                                 'rejete'     => 'Rejeté',
                                 default      => e($r['statut']),
                              };
                           ?>
                           <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>

   <!-- Commentaires en attente -->
   <div class="col-12 col-xl-5">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-chat-left-text-fill me-2 text-warning"></i>Commentaires à modérer</h6>
            <a href="commentaires.php" class="btn btn-sm btn-outline-warning">Voir tout</a>
         </div>
         <div class="card-body p-0">
            <?php if (empty($derniersCommentaires)): ?>
               <p class="text-muted text-center py-4">
                  <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-2"></i>
                  Aucun commentaire en attente.
               </p>
            <?php else: ?>
            <ul class="list-group list-group-flush">
               <?php foreach ($derniersCommentaires as $c): ?>
               <li class="list-group-item">
                  <div class="d-flex justify-content-between align-items-start">
                     <div class="me-2">
                        <span class="fw-semibold"><?= e($c['pseudo']) ?></span>
                        <span class="text-muted small"> sur <em><?= e($c['titre_recette']) ?></em></span>
                        <p class="mb-1 small text-muted text-truncate" style="max-width:240px">
                           <?= e($c['contenu']) ?>
                        </p>
                     </div>
                     <a href="commentaires.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning flex-shrink-0">
                        <i class="bi bi-eye"></i>
                     </a>
                  </div>
               </li>
               <?php endforeach; ?>
            </ul>
            <?php endif; ?>
         </div>
      </div>
   </div>

</div>

<!-- ── Ligne 3 : Derniers inscrits ───────────────────────────────────────── -->
<div class="row g-3">
   <div class="col-12">
      <div class="card border-0 shadow-sm">
         <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Derniers utilisateurs inscrits</h6>
            <a href="utilisateurs.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
         </div>
         <div class="card-body p-0">
            <?php if (empty($derniersUsers)): ?>
               <p class="text-muted text-center py-4">Aucun utilisateur inscrit.</p>
            <?php else: ?>
            <div class="table-responsive">
               <table class="table table-hover mb-0">
                  <thead class="table-light">
                     <tr>
                        <th>#</th>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($derniersUsers as $u): ?>
                     <tr>
                        <td class="text-muted small"><?= $u['id'] ?></td>
                        <td class="fw-semibold"><?= e($u['pseudo']) ?></td>
                        <td class="text-muted small"><?= e($u['email']) ?></td>
                        <td>
                           <?php if ($u['role'] === 'admin'): ?>
                              <span class="badge bg-danger">Admin</span>
                           <?php else: ?>
                              <span class="badge bg-secondary">Membre</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                           <?= date('d/m/Y', strtotime($u['date_creation'])) ?>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
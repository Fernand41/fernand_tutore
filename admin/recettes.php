<?php
/**
 * admin/recettes.php
 * Liste, modération et gestion des recettes
 */

require_once '../config/database.php';
require_once '../includes/session.php';

$pdo = Database::getInstance();

// ── Actions POST (modération) - AVANT le header ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('recettes.php');
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($id > 0 && in_array($action, ['publier', 'rejeter', 'brouillon', 'supprimer'])) {
        try {
            if ($action === 'supprimer') {
                $pdo->prepare("DELETE FROM recettes WHERE id = ?")->execute([$id]);
                setFlash('success', 'Recette supprimée avec succès.');
            } else {
                $statut = match($action) {
                    'publier'   => 'publie',
                    'rejeter'   => 'rejete',
                    'brouillon' => 'brouillon',
                };
                $datePubli = $statut === 'publie' ? date('Y-m-d H:i:s') : null;
                $stmt = $pdo->prepare("UPDATE recettes SET statut = ?, date_publication = ? WHERE id = ?");
                $stmt->execute([$statut, $datePubli, $id]);
                setFlash('success', 'Statut de la recette mis à jour.');
            }
        } catch (Exception $e) {
            setFlash('danger', 'Erreur lors de l\'action : ' . $e->getMessage());
        }
    }
    header('Location: recettes.php');
    exit;
}

$adminPageTitle  = 'Recettes | Goûts du Bénin';
$adminActivePage = 'recettes';
$breadcrumb      = [['label' => 'Recettes', 'url' => 'recettes.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

// ── Filtres ──────────────────────────────────────────────────────────────────
$filtreStatut    = $_GET['statut']    ?? '';
$filtreCategorie = $_GET['categorie'] ?? '';
$recherche       = trim($_GET['q']    ?? '');
$page            = max(1, (int)($_GET['page'] ?? 1));
$parPage         = 15;
$offset          = ($page - 1) * $parPage;

// Construction de la requête
$where  = ['1=1'];
$params = [];

if ($filtreStatut && in_array($filtreStatut, ['brouillon','en_attente','publie','rejete'])) {
    $where[]  = 'r.statut = ?';
    $params[] = $filtreStatut;
}
if ($filtreCategorie) {
    $where[]  = 'c.slug = ?';
    $params[] = $filtreCategorie;
}
if ($recherche) {
    $where[]  = '(r.titre LIKE ? OR u.pseudo LIKE ?)';
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}

$whereClause = implode(' AND ', $where);

$total = (int) $pdo->prepare("
    SELECT COUNT(*) FROM recettes r
    JOIN utilisateurs u ON r.id_auteur = u.id
    JOIN categories_recettes c ON r.id_categorie = c.id
    WHERE $whereClause
")->execute($params) ? $pdo->prepare("
    SELECT COUNT(*) FROM recettes r
    JOIN utilisateurs u ON r.id_auteur = u.id
    JOIN categories_recettes c ON r.id_categorie = c.id
    WHERE $whereClause
") : null;

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM recettes r
    JOIN utilisateurs u ON r.id_auteur = u.id
    JOIN categories_recettes c ON r.id_categorie = c.id
    WHERE $whereClause
");
$stmtCount->execute($params);
$total    = (int) $stmtCount->fetchColumn();
$nbPages  = max(1, (int) ceil($total / $parPage));

$stmtList = $pdo->prepare("
    SELECT r.id, r.titre, r.slug, r.statut, r.note_moyenne, r.nb_notes,
           r.difficulte, r.date_creation, r.date_publication,
           u.pseudo AS auteur,
           c.nom    AS categorie, c.slug AS categorie_slug
    FROM recettes r
    JOIN utilisateurs u ON r.id_auteur = u.id
    JOIN categories_recettes c ON r.id_categorie = c.id
    WHERE $whereClause
    ORDER BY r.date_creation DESC
    LIMIT $parPage OFFSET $offset
");
$stmtList->execute($params);
$recettes = $stmtList->fetchAll();

// Catégories pour le filtre
$categories = $pdo->query("SELECT nom, slug FROM categories_recettes ORDER BY nom")->fetchAll();

// Compteurs par statut
$compteurs = [];
foreach (['brouillon','en_attente','publie','rejete'] as $s) {
    $compteurs[$s] = (int) $pdo->prepare("SELECT COUNT(*) FROM recettes WHERE statut = ?")->execute([$s])
        ? (int) $pdo->query("SELECT COUNT(*) FROM recettes WHERE statut = '$s'")->fetchColumn()
        : 0;
}
// Recompute properly
$stmtCpt = $pdo->query("SELECT statut, COUNT(*) AS n FROM recettes GROUP BY statut");
foreach ($stmtCpt->fetchAll() as $row) {
    $compteurs[$row['statut']] = (int) $row['n'];
}
?>

<!-- Barre de filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Titre ou auteur..." value="<?= e($recherche) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <?php foreach (['en_attente'=>'En attente','publie'=>'Publié','rejete'=>'Rejeté','brouillon'=>'Brouillon'] as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $filtreStatut === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Catégorie</label>
                <select name="categorie" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $filtreCategorie === $cat['slug'] ? 'selected' : '' ?>>
                        <?= e($cat['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="recettes.php" class="btn btn-outline-secondary btn-sm">✕</a>
            </div>
        </form>
    </div>
</div>

<!-- Badges statuts -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="recettes.php" class="badge text-bg-secondary text-decoration-none fs-6 px-3 py-2">
        Toutes (<?= array_sum($compteurs) ?>)
    </a>
    <a href="recettes.php?statut=en_attente" class="badge text-bg-warning text-decoration-none fs-6 px-3 py-2">
        En attente (<?= $compteurs['en_attente'] ?? 0 ?>)
    </a>
    <a href="recettes.php?statut=publie" class="badge text-bg-success text-decoration-none fs-6 px-3 py-2">
        Publiées (<?= $compteurs['publie'] ?? 0 ?>)
    </a>
    <a href="recettes.php?statut=rejete" class="badge text-bg-danger text-decoration-none fs-6 px-3 py-2">
        Rejetées (<?= $compteurs['rejete'] ?? 0 ?>)
    </a>
    <a href="recettes.php?statut=brouillon" class="badge text-bg-secondary text-decoration-none fs-6 px-3 py-2">
        Brouillons (<?= $compteurs['brouillon'] ?? 0 ?>)
    </a>
</div>

<!-- Table recettes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-journal-text me-2 text-success"></i>
            <?= $total ?> recette<?= $total > 1 ? 's' : '' ?> trouvée<?= $total > 1 ? 's' : '' ?>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recettes)): ?>
            <p class="text-muted text-center py-5">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Aucune recette trouvée.
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Difficulté</th>
                        <th>Note</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recettes as $r): ?>
                    <tr>
                        <td class="text-muted small"><?= $r['id'] ?></td>
                        <td>
                            <span class="fw-semibold"><?= e($r['titre']) ?></span>
                        </td>
                        <td class="text-muted small"><?= e($r['auteur']) ?></td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($r['categorie']) ?></span>
                        </td>
                        <td>
                            <?php $dcls = ['facile'=>'success','moyen'=>'warning','difficile'=>'danger'][$r['difficulte']] ?? 'secondary'; ?>
                            <span class="badge text-bg-<?= $dcls ?>"><?= ucfirst($r['difficulte']) ?></span>
                        </td>
                        <td>
                            <?php if ($r['nb_notes'] > 0): ?>
                                <span class="text-warning">★</span>
                                <small><?= number_format($r['note_moyenne'], 1) ?>/5</small>
                                <small class="text-muted">(<?= $r['nb_notes'] ?>)</small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $cls = ['publie'=>'success','en_attente'=>'warning text-dark','rejete'=>'danger','brouillon'=>'secondary'][$r['statut']] ?? 'secondary';
                            $lbl = ['publie'=>'Publié','en_attente'=>'En attente','rejete'=>'Rejeté','brouillon'=>'Brouillon'][$r['statut']] ?? $r['statut'];
                            ?>
                            <span class="badge text-bg-<?= $cls ?>"><?= $lbl ?></span>
                        </td>
                        <td class="text-muted small">
                            <?= $r['date_publication']
                                ? date('d/m/Y', strtotime($r['date_publication']))
                                : date('d/m/Y', strtotime($r['date_creation'])) ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <?php if ($r['statut'] !== 'publie'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="publier">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm" title="Publier">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($r['statut'] !== 'rejete'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="rejeter">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Rejeter">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer définitivement cette recette ?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($nbPages > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($p = 1; $p <= $nbPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&statut=<?= e($filtreStatut) ?>&categorie=<?= e($filtreCategorie) ?>&q=<?= e($recherche) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
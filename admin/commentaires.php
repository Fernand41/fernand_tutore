<?php
/**
 * admin/commentaires.php
 * Modération des commentaires
 */

require_once '../includes/session.php';
require_once '../config/database.php';

$pdo = Database::getInstance();

// ── Actions POST ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin('../front_end/login.php');
    verifyCsrf('commentaires.php');
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($id > 0 && in_array($action, ['approuver', 'rejeter', 'supprimer'])) {
        try {
            if ($action === 'supprimer') {
                $pdo->prepare("DELETE FROM commentaires WHERE id = ?")->execute([$id]);
                setFlash('success', 'Commentaire supprimé.');
            } else {
                $statut = $action === 'approuver' ? 'approuve' : 'rejete';
                $pdo->prepare("UPDATE commentaires SET statut = ? WHERE id = ?")->execute([$statut, $id]);
                setFlash('success', 'Commentaire ' . ($statut === 'approuve' ? 'approuvé' : 'rejeté') . '.');
            }
        } catch (Exception $e) {
            setFlash('danger', 'Erreur : ' . $e->getMessage());
        }
    }
    header('Location: commentaires.php');
    exit;
}

$adminPageTitle  = 'Commentaires | Goûts du Bénin';
$adminActivePage = 'commentaires';
$breadcrumb      = [['label' => 'Commentaires', 'url' => 'commentaires.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

// ── Filtres ──────────────────────────────────────────────────────────────────
$filtreStatut = $_GET['statut'] ?? '';
if ($filtreStatut === 'all') {
    $filtreStatut = '';
}
$recherche    = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$parPage      = 20;
$offset       = ($page - 1) * $parPage;

$where  = ['1=1'];
$params = [];

if ($filtreStatut && in_array($filtreStatut, ['en_attente','approuve','rejete'])) {
    $where[]  = 'c.statut = ?';
    $params[] = $filtreStatut;
}
if ($recherche) {
    $where[]  = '(c.contenu LIKE ? OR u.pseudo LIKE ? OR r.titre LIKE ?)';
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}
$whereClause = implode(' AND ', $where);

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM commentaires c
    JOIN utilisateurs u ON c.id_utilisateur = u.id
    JOIN recettes r ON c.id_recette = r.id
    WHERE $whereClause
");
$stmtCount->execute($params);
$total   = (int) $stmtCount->fetchColumn();
$nbPages = max(1, (int) ceil($total / $parPage));

$stmtList = $pdo->prepare("
    SELECT c.id, c.contenu, c.statut, c.date_creation,
           u.pseudo AS auteur, u.id AS id_utilisateur,
           r.titre AS titre_recette, r.id AS id_recette
    FROM commentaires c
    JOIN utilisateurs u ON c.id_utilisateur = u.id
    JOIN recettes r ON c.id_recette = r.id
    WHERE $whereClause
    ORDER BY c.date_creation DESC
    LIMIT $parPage OFFSET $offset
");
$stmtList->execute($params);
$commentaires = $stmtList->fetchAll();

// Compteurs par statut (uniquement commentaires associés à un utilisateur et une recette)
$stmtCpt = $pdo->query(
    "SELECT c.statut, COUNT(*) AS n FROM commentaires c
     JOIN utilisateurs u ON c.id_utilisateur = u.id
     JOIN recettes r ON c.id_recette = r.id
     GROUP BY c.statut"
);
$compteurs = [];
foreach ($stmtCpt->fetchAll() as $row) {
    $compteurs[$row['statut']] = (int) $row['n'];
}
?>

<!-- Barre de filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Contenu, auteur, recette..." value="<?= e($recherche) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Statut</label>
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="en_attente" <?= $filtreStatut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="approuve"   <?= $filtreStatut === 'approuve'   ? 'selected' : '' ?>>Approuvés</option>
                    <option value="rejete"     <?= $filtreStatut === 'rejete'     ? 'selected' : '' ?>>Rejetés</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="commentaires.php" class="btn btn-outline-secondary btn-sm">✕</a>
            </div>
        </form>
    </div>
</div>

<!-- Badges -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="commentaires.php?statut=en_attente" class="badge <?= $filtreStatut === 'en_attente' ? 'bg-dark text-white' : 'text-bg-warning' ?> text-decoration-none fs-6 px-3 py-2">
        En attente (<?= $compteurs['en_attente'] ?? 0 ?>)
    </a>
    <a href="commentaires.php?statut=approuve" class="badge <?= $filtreStatut === 'approuve' ? 'bg-dark text-white' : 'text-bg-success' ?> text-decoration-none fs-6 px-3 py-2">
        Approuvés (<?= $compteurs['approuve'] ?? 0 ?>)
    </a>
    <a href="commentaires.php?statut=rejete" class="badge <?= $filtreStatut === 'rejete' ? 'bg-dark text-white' : 'text-bg-danger' ?> text-decoration-none fs-6 px-3 py-2">
        Rejetés (<?= $compteurs['rejete'] ?? 0 ?>)
    </a>
    <a href="commentaires.php?statut=all" class="badge <?= $filtreStatut === '' ? 'bg-dark text-white' : 'text-bg-secondary' ?> text-decoration-none fs-6 px-3 py-2">
        Tous (<?= array_sum($compteurs) ?>)
    </a>
</div>

<!-- Liste commentaires -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-chat-left-text-fill me-2 text-warning"></i>
            <?= $total ?> commentaire<?= $total > 1 ? 's' : '' ?>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($commentaires)): ?>
            <p class="text-muted text-center py-5">
                <i class="bi bi-chat-left fs-2 d-block mb-2"></i>
                Aucun commentaire à afficher.
            </p>
        <?php else: ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($commentaires as $c): ?>
            <li class="list-group-item py-3">
                <div class="row align-items-start g-3">
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold"><?= e($c['auteur']) ?></span>
                            <span class="text-muted small">sur</span>
                            <em class="text-muted small"><?= e($c['titre_recette']) ?></em>
                            <?php
                            $cls = ['en_attente'=>'warning text-dark','approuve'=>'success','rejete'=>'danger'][$c['statut']] ?? 'secondary';
                            $lbl = ['en_attente'=>'En attente','approuve'=>'Approuvé','rejete'=>'Rejeté'][$c['statut']] ?? $c['statut'];
                            ?>
                            <span class="badge text-bg-<?= $cls ?> ms-2"><?= $lbl ?></span>
                            <span class="text-muted small ms-auto">
                                <?= date('d/m/Y H:i', strtotime($c['date_creation'])) ?>
                            </span>
                        </div>
                        <p class="mb-0 text-muted" style="font-size:.92rem; line-height:1.5;">
                            <?= nl2br(e($c['contenu'])) ?>
                        </p>
                    </div>
                    <div class="col-auto d-flex gap-1 flex-wrap">
                        <?php if ($c['statut'] !== 'approuve'): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="approuver">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm" title="Approuver">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <?php if ($c['statut'] !== 'rejete'): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="rejeter">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm" title="Rejeter">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Supprimer ce commentaire ?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <?php if ($nbPages > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($p = 1; $p <= $nbPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&statut=<?= e($filtreStatut) ?>&q=<?= e($recherche) ?>">
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
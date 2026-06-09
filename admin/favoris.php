<?php
/**
 * admin/favoris.php
 * Gestion des favoris et des notes utilisateur.
 */

require_once '../config/database.php';

$adminPageTitle  = 'Favoris & Notes | Goûts du Bénin';
$adminActivePage = 'favoris';
$breadcrumb      = [['label' => 'Favoris & Notes', 'url' => 'favoris.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

$pdo = Database::getInstance();

function refreshRecipeRatings(PDO $pdo, int $recipeId): void {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(ROUND(AVG(valeur),1), 0) AS avg_val FROM notes WHERE id_recette = ?");
    $stmt->execute([$recipeId]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("UPDATE recettes SET nb_notes = ?, note_moyenne = ? WHERE id = ?")
            ->execute([(int)$row['cnt'], (float)$row['avg_val'], $recipeId]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('favoris.php');
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        setFlash('danger', 'Identifiant invalide.');
        header('Location: favoris.php');
        exit;
    }

    try {
        switch ($action) {
            case 'supprimer_favori':
                $pdo->prepare('DELETE FROM favoris WHERE id = ?')->execute([$id]);
                setFlash('success', 'Favori supprimé.');
                break;

            case 'supprimer_note':
                $stmt = $pdo->prepare('SELECT id_recette FROM notes WHERE id = ?');
                $stmt->execute([$id]);
                $note = $stmt->fetch();
                if ($note) {
                    $pdo->prepare('DELETE FROM notes WHERE id = ?')->execute([$id]);
                    refreshRecipeRatings($pdo, (int) $note['id_recette']);
                    setFlash('success', 'Note supprimée et statistiques mises à jour.');
                } else {
                    setFlash('danger', 'Note introuvable.');
                }
                break;

            case 'modifier_note':
                $valeur = max(1, min(5, (int) ($_POST['valeur'] ?? 0)));
                $stmt   = $pdo->prepare('SELECT id_recette FROM notes WHERE id = ?');
                $stmt->execute([$id]);
                $note = $stmt->fetch();
                if ($note) {
                    $pdo->prepare('UPDATE notes SET valeur = ? WHERE id = ?')->execute([$valeur, $id]);
                    refreshRecipeRatings($pdo, (int) $note['id_recette']);
                    setFlash('success', 'Note mise à jour.');
                } else {
                    setFlash('danger', 'Note introuvable.');
                }
                break;
        }
    } catch (Exception $e) {
        setFlash('danger', 'Erreur : ' . $e->getMessage());
    }

    header('Location: favoris.php');
    exit;
}

$tab      = in_array($_GET['tab'] ?? 'favoris', ['favoris', 'notes'], true) ? $_GET['tab'] : 'favoris';
$recherche = trim($_GET['q'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$parPage   = 20;
$offset    = ($page - 1) * $parPage;

// Récapitulatif
$summary = [
    'favoris' => 0,
    'notes'   => 0,
    'avg'     => 0,
];
try {
    $summary['favoris'] = (int) $pdo->query('SELECT COUNT(*) FROM favoris')->fetchColumn();
} catch (Exception $e) {
    $summary['favoris'] = 0;
}
try {
    $summary['notes'] = (int) $pdo->query('SELECT COUNT(*) FROM notes')->fetchColumn();
} catch (Exception $e) {
    $summary['notes'] = 0;
}
try {
    $summary['avg'] = (float) $pdo->query('SELECT COALESCE(ROUND(AVG(valeur),1), 0) FROM notes')->fetchColumn();
} catch (Exception $e) {
    $summary['avg'] = 0;
}

$topFavorited = [];
try {
    $topFavorited = $pdo->query(
        'SELECT r.id, r.titre, COUNT(f.id) AS nb_favoris
         FROM recettes r
         JOIN favoris f ON f.id_recette = r.id
         GROUP BY r.id
         ORDER BY nb_favoris DESC
         LIMIT 5'
    )->fetchAll();
} catch (Exception $e) {
    $topFavorited = [];
}

$filters = [];
$params  = [];
$where   = '1=1';
if ($recherche !== '') {
    $where = '(u.pseudo LIKE ? OR r.titre LIKE ? OR c.nom LIKE ?)';
    $params = ["%$recherche%", "%$recherche%", "%$recherche%"];
}

$favoris = [];
$notes   = [];
$total   = 0;

if ($tab === 'favoris') {
    try {
        $stmtCount = $pdo->prepare(
            "SELECT COUNT(*) FROM favoris f
             JOIN utilisateurs u ON f.id_utilisateur = u.id
             JOIN recettes r ON f.id_recette = r.id
             LEFT JOIN categories_recettes c ON r.id_categorie = c.id
             WHERE $where"
        );
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $stmtList = $pdo->prepare(
            "SELECT f.id, f.date_ajout, u.pseudo AS utilisateur, r.id AS id_recette, r.titre AS recette,
                    c.nom AS categorie
             FROM favoris f
             JOIN utilisateurs u ON f.id_utilisateur = u.id
             JOIN recettes r ON f.id_recette = r.id
             LEFT JOIN categories_recettes c ON r.id_categorie = c.id
             WHERE $where
             ORDER BY f.date_ajout DESC
             LIMIT $parPage OFFSET $offset"
        );
        $stmtList->execute($params);
        $favoris = $stmtList->fetchAll();
    } catch (Exception $e) {
        $favoris = [];
        $total   = 0;
    }
} else {
    try {
        $stmtCount = $pdo->prepare(
            "SELECT COUNT(*) FROM notes n
             JOIN utilisateurs u ON n.id_utilisateur = u.id
             JOIN recettes r ON n.id_recette = r.id
             LEFT JOIN categories_recettes c ON r.id_categorie = c.id
             WHERE $where"
        );
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $stmtList = $pdo->prepare(
            "SELECT n.id, n.valeur, n.date_creation, u.pseudo AS utilisateur, r.id AS id_recette, r.titre AS recette,
                    c.nom AS categorie
             FROM notes n
             JOIN utilisateurs u ON n.id_utilisateur = u.id
             JOIN recettes r ON n.id_recette = r.id
             LEFT JOIN categories_recettes c ON r.id_categorie = c.id
             WHERE $where
             ORDER BY n.date_creation DESC
             LIMIT $parPage OFFSET $offset"
        );
        $stmtList->execute($params);
        $notes = $stmtList->fetchAll();
    } catch (Exception $e) {
        $notes = [];
        $total = 0;
    }
}

$nbPages = max(1, (int) ceil($total / $parPage));
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100 bg-info-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-heart-fill fs-3 text-info"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-info"><?= $summary['favoris'] ?></div>
                        <div class="text-muted small">Favoris enregistrés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100 bg-warning-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-star-fill fs-3 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning"><?= $summary['notes'] ?></div>
                        <div class="text-muted small">Notes enregistrées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100 bg-success-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-star-half fs-3 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success"><?= number_format($summary['avg'], 1) ?>/5</div>
                        <div class="text-muted small">Note moyenne globale</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'favoris' ? 'active' : '' ?>" href="?tab=favoris&q=<?= e($recherche) ?>">Favoris</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'notes' ? 'active' : '' ?>" href="?tab=notes&q=<?= e($recherche) ?>">Notes</a>
            </li>
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Utilisateur, recette, catégorie..." value="<?= e($recherche) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="favoris.php" class="btn btn-outline-secondary btn-sm">✕</a>
            </div>
        </form>
    </div>
</div>

<?php if ($tab === 'favoris'): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-heart-fill me-2 text-info"></i>Favoris</h6>
        <span class="text-muted small"><?= $total ?> favori<?= $total > 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($favoris)): ?>
            <p class="text-muted text-center py-5">Aucun favori trouvé.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Recette</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($favoris as $fav): ?>
                    <tr>
                        <td class="text-muted small"><?= e($fav['utilisateur']) ?></td>
                        <td>
                            <a href="recettes.php?id=<?= $fav['id_recette'] ?>" class="text-decoration-none">
                                <?= e($fav['recette']) ?>
                            </a>
                        </td>
                        <td class="text-muted small"><?= e($fav['categorie'] ?: '—') ?></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($fav['date_ajout'])) ?></td>
                        <td class="text-end">
                            <form method="POST" onsubmit="return confirm('Supprimer ce favori ?')" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="supprimer_favori">
                                <input type="hidden" name="id" value="<?= $fav['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($nbPages > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($p = 1; $p <= $nbPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=favoris&page=<?= $p ?>&q=<?= e($recherche) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-star-fill me-2 text-warning"></i>Notes</h6>
        <span class="text-muted small"><?= $total ?> note<?= $total > 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($notes)): ?>
            <p class="text-muted text-center py-5">Aucune note trouvée.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Recette</th>
                        <th>Catégorie</th>
                        <th>Valeur</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                    <tr>
                        <td class="text-muted small"><?= e($note['utilisateur']) ?></td>
                        <td>
                            <a href="recettes.php?id=<?= $note['id_recette'] ?>" class="text-decoration-none">
                                <?= e($note['recette']) ?>
                            </a>
                        </td>
                        <td class="text-muted small"><?= e($note['categorie'] ?: '—') ?></td>
                        <td>
                            <span class="badge bg-warning text-dark"><?= e($note['valeur']) ?>/5</span>
                        </td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($note['date_creation'])) ?></td>
                        <td class="text-end">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="modifier_note">
                                <input type="hidden" name="id" value="<?= $note['id'] ?>">
                                <select name="valeur" class="form-select form-select-sm d-inline w-auto">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= $note['valeur'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="btn btn-success btn-sm" title="Mettre à jour">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Supprimer cette note ?')" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="supprimer_note">
                                <input type="hidden" name="id" value="<?= $note['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($nbPages > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($p = 1; $p <= $nbPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=notes&page=<?= $p ?>&q=<?= e($recherche) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/admin_footer.php'; ?>

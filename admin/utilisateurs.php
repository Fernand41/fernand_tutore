<?php
/**
 * admin/utilisateurs.php
 * Gestion des utilisateurs (liste, activation, rôle, suppression)
 */

require_once '../config/database.php';

$adminPageTitle  = 'Utilisateurs | Goûts du Bénin';
$adminActivePage = 'utilisateurs';
$breadcrumb      = [['label' => 'Utilisateurs', 'url' => 'utilisateurs.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

$pdo = Database::getInstance();

// ── Actions POST ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('utilisateurs.php');
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    // Ne pas agir sur soi-même
    if ($id > 0 && $id !== currentUserId()) {
        try {
            switch ($action) {
                case 'activer':
                    $pdo->prepare("UPDATE utilisateurs SET est_actif = 1 WHERE id = ?")->execute([$id]);
                    setFlash('success', 'Utilisateur activé.');
                    break;
                case 'desactiver':
                    $pdo->prepare("UPDATE utilisateurs SET est_actif = 0 WHERE id = ?")->execute([$id]);
                    setFlash('success', 'Utilisateur désactivé.');
                    break;
                case 'promouvoir':
                    $pdo->prepare("UPDATE utilisateurs SET role = 'admin' WHERE id = ?")->execute([$id]);
                    setFlash('success', 'Utilisateur promu administrateur.');
                    break;
                case 'retrograder':
                    $pdo->prepare("UPDATE utilisateurs SET role = 'user' WHERE id = ?")->execute([$id]);
                    setFlash('success', 'Utilisateur rétrogradé en membre.');
                    break;
                case 'supprimer':
                    $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$id]);
                    setFlash('success', 'Utilisateur supprimé.');
                    break;
            }
        } catch (Exception $e) {
            setFlash('danger', 'Erreur : ' . $e->getMessage());
        }
    } elseif ($id === currentUserId()) {
        setFlash('warning', 'Vous ne pouvez pas modifier votre propre compte ici.');
    }
    header('Location: utilisateurs.php');
    exit;
}

// ── Filtres ──────────────────────────────────────────────────────────────────
$filtreRole   = $_GET['role']   ?? '';
$filtreActif  = $_GET['actif']  ?? '';
$recherche    = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$parPage      = 20;
$offset       = ($page - 1) * $parPage;

$where  = ['1=1'];
$params = [];

if ($filtreRole && in_array($filtreRole, ['user','admin'])) {
    $where[]  = 'role = ?';
    $params[] = $filtreRole;
}
if ($filtreActif !== '') {
    $where[]  = 'est_actif = ?';
    $params[] = (int) $filtreActif;
}
if ($recherche) {
    $where[]  = '(pseudo LIKE ? OR email LIKE ?)';
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}
$whereClause = implode(' AND ', $where);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE $whereClause");
$stmtCount->execute($params);
$total   = (int) $stmtCount->fetchColumn();
$nbPages = max(1, (int) ceil($total / $parPage));

$stmtList = $pdo->prepare("
    SELECT u.id, u.pseudo, u.email, u.role, u.est_actif, u.date_creation,
           COUNT(r.id) AS nb_recettes
    FROM utilisateurs u
    LEFT JOIN recettes r ON r.id_auteur = u.id
    WHERE $whereClause
    GROUP BY u.id
    ORDER BY u.date_creation DESC
    LIMIT $parPage OFFSET $offset
");
$stmtList->execute($params);
$utilisateurs = $stmtList->fetchAll();

// Compteurs rapides
$nbAdmins  = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin'")->fetchColumn();
$nbActifs  = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE est_actif = 1")->fetchColumn();
$nbTotal   = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
?>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3 bg-primary-subtle">
            <div class="fs-2 fw-bold text-primary"><?= $nbTotal ?></div>
            <div class="text-muted small">Total inscrits</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3 bg-success-subtle">
            <div class="fs-2 fw-bold text-success"><?= $nbActifs ?></div>
            <div class="text-muted small">Comptes actifs</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3 bg-danger-subtle">
            <div class="fs-2 fw-bold text-danger"><?= $nbAdmins ?></div>
            <div class="text-muted small">Administrateurs</div>
        </div>
    </div>
</div>

<!-- Barre de filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Pseudo ou email..." value="<?= e($recherche) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Rôle</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="user"  <?= $filtreRole === 'user'  ? 'selected' : '' ?>>Membres</option>
                    <option value="admin" <?= $filtreRole === 'admin' ? 'selected' : '' ?>>Admins</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Statut</label>
                <select name="actif" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="1" <?= $filtreActif === '1' ? 'selected' : '' ?>>Actifs</option>
                    <option value="0" <?= $filtreActif === '0' ? 'selected' : '' ?>>Désactivés</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="utilisateurs.php" class="btn btn-outline-secondary btn-sm">✕</a>
            </div>
        </form>
    </div>
</div>

<!-- Table utilisateurs -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            <?= $total ?> utilisateur<?= $total > 1 ? 's' : '' ?>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($utilisateurs)): ?>
            <p class="text-muted text-center py-5">Aucun utilisateur trouvé.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Recettes</th>
                        <th>Inscrit le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <?php $isSelf = $u['id'] === currentUserId(); ?>
                    <tr class="<?= !$u['est_actif'] ? 'table-secondary opacity-75' : '' ?>">
                        <td class="text-muted small"><?= $u['id'] ?></td>
                        <td>
                            <span class="fw-semibold"><?= e($u['pseudo']) ?></span>
                            <?php if ($isSelf): ?>
                                <span class="badge bg-info text-dark ms-1">Vous</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= e($u['email']) ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge text-bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Membre</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['est_actif']): ?>
                                <span class="badge text-bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Désactivé</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $u['nb_recettes'] ?></span>
                        </td>
                        <td class="text-muted small">
                            <?= date('d/m/Y', strtotime($u['date_creation'])) ?>
                        </td>
                        <td class="text-end">
                            <?php if (!$isSelf): ?>
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                <!-- Activation / Désactivation -->
                                <?php if ($u['est_actif']): ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="desactiver">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Désactiver">
                                        <i class="bi bi-pause-fill"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="activer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm" title="Activer">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <a href="utilisateur.php?id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm" title="Voir / modifier">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- Promotion / Rétrogradation -->
                                <?php if ($u['role'] !== 'admin'): ?>
                                <form method="POST" onsubmit="return confirm('Promouvoir comme administrateur ?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="promouvoir">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Promouvoir admin">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" onsubmit="return confirm('Rétrograder en membre ?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="retrograder">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Rétrograder">
                                        <i class="bi bi-arrow-down-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Suppression -->
                                <form method="POST" onsubmit="return confirm('Supprimer définitivement cet utilisateur et ses recettes ?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
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
                    <a class="page-link"
                       href="?page=<?= $p ?>&role=<?= e($filtreRole) ?>&actif=<?= e($filtreActif) ?>&q=<?= e($recherche) ?>">
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
<?php
/**
 * admin/categories.php
 * Gestion des catégories de recettes (CRUD complet)
 */

require_once '../config/database.php';

$adminPageTitle  = 'Catégories | Goûts du Bénin';
$adminActivePage = 'categories';
$breadcrumb      = [['label' => 'Catégories', 'url' => 'categories.php']];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

$pdo = Database::getInstance();

// ── Helpers ───────────────────────────────────────────────────────────────────
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $from = ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç','ñ',' ','/','\'','"','&'];
    $to   = ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c','n','-','-','-','-','et'];
    $text = str_replace($from, $to, $text);
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ── Actions POST ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('categories.php');
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'ajouter':
                $nom  = trim($_POST['nom'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                if (empty($nom)) {
                    setFlash('danger', 'Le nom est obligatoire.');
                    break;
                }
                $slug = slugify($nom);
                // S'assurer de l'unicité du slug
                $existing = (int) $pdo->prepare("SELECT COUNT(*) FROM categories_recettes WHERE slug = ?")->execute([$slug])
                    ? $pdo->query("SELECT COUNT(*) FROM categories_recettes WHERE slug = '$slug'")->fetchColumn() : 0;

                $chk = $pdo->prepare("SELECT COUNT(*) FROM categories_recettes WHERE slug = ?");
                $chk->execute([$slug]);
                if ((int) $chk->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }
                $stmt = $pdo->prepare("INSERT INTO categories_recettes (nom, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $slug, $desc ?: null]);
                setFlash('success', "Catégorie « $nom » ajoutée.");
                break;

            case 'modifier':
                $id   = (int) ($_POST['id'] ?? 0);
                $nom  = trim($_POST['nom'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                if ($id <= 0 || empty($nom)) {
                    setFlash('danger', 'Données invalides.');
                    break;
                }
                $slug = slugify($nom);
                $chk  = $pdo->prepare("SELECT COUNT(*) FROM categories_recettes WHERE slug = ? AND id != ?");
                $chk->execute([$slug, $id]);
                if ((int) $chk->fetchColumn() > 0) {
                    $slug .= '-' . $id;
                }
                $stmt = $pdo->prepare("UPDATE categories_recettes SET nom = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$nom, $slug, $desc ?: null, $id]);
                setFlash('success', "Catégorie mise à jour.");
                break;

            case 'supprimer':
                $id = (int) ($_POST['id'] ?? 0);
                // Vérifier si des recettes sont liées
                $nb = (int) $pdo->prepare("SELECT COUNT(*) FROM recettes WHERE id_categorie = ?")
                    ->execute([$id]) ? 0 : 0;
                $chkR = $pdo->prepare("SELECT COUNT(*) FROM recettes WHERE id_categorie = ?");
                $chkR->execute([$id]);
                if ((int) $chkR->fetchColumn() > 0) {
                    setFlash('danger', 'Impossible de supprimer : des recettes utilisent cette catégorie.');
                    break;
                }
                $pdo->prepare("DELETE FROM categories_recettes WHERE id = ?")->execute([$id]);
                setFlash('success', 'Catégorie supprimée.');
                break;
        }
    } catch (Exception $e) {
        setFlash('danger', 'Erreur : ' . $e->getMessage());
    }
    header('Location: categories.php');
    exit;
}

// ── Données ───────────────────────────────────────────────────────────────────
$categories = $pdo->query("
    SELECT c.id, c.nom, c.slug, c.description,
           COUNT(r.id) AS nb_recettes
    FROM categories_recettes c
    LEFT JOIN recettes r ON r.id_categorie = c.id
    GROUP BY c.id
    ORDER BY c.nom ASC
")->fetchAll();
?>

<div class="row g-4">
    <!-- Formulaire d'ajout -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-plus-circle-fill me-2 text-success"></i>
                    Ajouter une catégorie
                </h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control form-control-sm"
                               placeholder="Ex: Entrées" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3"
                                  placeholder="Courte description (optionnel)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste des catégories -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-tags-fill me-2 text-danger"></i>
                    <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($categories)): ?>
                    <p class="text-muted text-center py-5">Aucune catégorie enregistrée.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th class="text-center">Recettes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($cat['nom']) ?></td>
                                <td>
                                    <code class="small"><?= e($cat['slug']) ?></code>
                                </td>
                                <td class="text-muted small">
                                    <?= $cat['description']
                                        ? mb_strimwidth(e($cat['description']), 0, 60, '…')
                                        : '—' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $cat['nb_recettes'] > 0 ? 'text-bg-success' : 'text-bg-light text-dark border' ?>">
                                        <?= $cat['nb_recettes'] ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <!-- Bouton modifier -->
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= $cat['id'] ?>"
                                            data-nom="<?= e($cat['nom']) ?>"
                                            data-description="<?= e($cat['description'] ?? '') ?>"
                                            title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($cat['nb_recettes'] == 0): ?>
                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette catégorie ?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled title="Impossible : recettes liées">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                    <?php endif; ?>
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

<!-- Modal de modification -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="bi bi-pencil me-2"></i>Modifier la catégorie
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="editNom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = [];
require_once '../includes/admin_footer.php';
?>
<script>
// Pré-remplir la modal d'édition
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value          = btn.dataset.id;
    document.getElementById('editNom').value         = btn.dataset.nom;
    document.getElementById('editDescription').value = btn.dataset.description;
});
</script>
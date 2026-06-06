<?php
/**
 * admin/utilisateur.php
 * Détail et édition d'un utilisateur par l'administrateur.
 */

require_once '../config/database.php';

$adminPageTitle  = 'Utilisateur | Goûts du Bénin';
$adminActivePage = 'utilisateurs';
$breadcrumb      = [
    ['label' => 'Utilisateurs', 'url' => 'utilisateurs.php'],
    ['label' => 'Détail utilisateur', 'url' => '#'],
];
$adminAssets     = '../back_end';

require_once '../includes/admin_header.php';

$pdo = Database::getInstance();

$id = max(0, (int) ($_GET['id'] ?? 0));
if ($id <= 0) {
    setFlash('danger', 'Utilisateur introuvable.');
    header('Location: utilisateurs.php');
    exit;
}

// Traitement du formulaire d'édition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf('utilisateur.php?id=' . $id);
    $pseudo   = trim($_POST['pseudo'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'user';
    $estActif = isset($_POST['est_actif']) ? 1 : 0;

    if ($pseudo === '' || $email === '') {
        setFlash('danger', 'Le pseudo et l’adresse e-mail sont obligatoires.');
        header('Location: utilisateur.php?id=' . $id);
        exit;
    }

    $allowedRoles = ['user', 'admin'];
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'user';
    }

    try {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET pseudo = ?, email = ?, role = ?, est_actif = ? WHERE id = ?");
        $stmt->execute([$pseudo, $email, $role, $estActif, $id]);
        setFlash('success', 'Informations utilisateur mises à jour.');
    } catch (Exception $e) {
        setFlash('danger', 'Impossible de mettre à jour l’utilisateur : ' . $e->getMessage());
    }

    header('Location: utilisateur.php?id=' . $id);
    exit;
}

$userStmt = $pdo->prepare("SELECT id, pseudo, email, role, est_actif, date_creation FROM utilisateurs WHERE id = ?");
$userStmt->execute([$id]);
$user = $userStmt->fetch();

if (!$user) {
    setFlash('danger', 'Utilisateur introuvable.');
    header('Location: utilisateurs.php');
    exit;
}

$recipesCountStmt = $pdo->prepare("SELECT COUNT(*) FROM recettes WHERE id_auteur = ?");
$recipesCountStmt->execute([$id]);
$recipesCount = (int) $recipesCountStmt->fetchColumn();

$roles = [
    'user'  => 'Membre',
    'admin' => 'Administrateur',
];
?>

<div class="row gy-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="avatar avatar-xl rounded-circle bg-success bg-opacity-10 text-success fw-bold" style="width:4rem;height:4rem;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;">
                        <?= e(substr($user['pseudo'], 0, 1)) ?>
                    </span>
                </div>
                <h5 class="fw-bold mb-1"><?= e($user['pseudo']) ?></h5>
                <p class="text-muted mb-2"><?= $roles[$user['role']] ?? ucfirst($user['role']) ?></p>
                <p class="text-muted small mb-1">Inscrit le <?= date('d/m/Y', strtotime($user['date_creation'])) ?></p>
                <p class="text-muted small mb-0">Recettes publiées : <?= $recipesCount ?></p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Statut du compte</h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-<?= $user['est_actif'] ? 'success' : 'secondary' ?>">
                        <?= $user['est_actif'] ? 'Actif' : 'Désactivé' ?>
                    </span>
                    <a href="utilisateurs.php" class="btn btn-sm btn-outline-secondary ms-auto">Retour à la liste</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Modifier l'utilisateur</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pseudo</label>
                        <input type="text" name="pseudo" class="form-control" value="<?= e($user['pseudo']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adresse e-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rôle</label>
                            <select name="role" class="form-select">
                                <?php foreach ($roles as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Compte actif</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="est_actif" name="est_actif" value="1" <?= $user['est_actif'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="est_actif">Activer le compte</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                        <a href="utilisateurs.php" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>

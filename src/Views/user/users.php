<?php
// Fichier : views/user/users.php
$users            = $users ?? [];
$userCountByRole  = $userCountByRole ?? [];
$totalActiveUsers = $totalActiveUsers ?? 0;

function userRoleBadgeClass(string $role): string
{
    $r = strtolower($role);
    if (str_contains($r, 'admin'))                                                            return 'role-admin';
    if (str_contains($r, 'pdg'))                                                              return 'role-pdg';
    if (str_contains($r, 'cdp') || str_contains($r, 'chef') || str_contains($r, 'project')) return 'role-cdp';
    if (str_contains($r, 'dev'))                                                              return 'role-dev';
    if (str_contains($r, 'design'))                                                           return 'role-designer';
    return 'role-default';
}
?>

<div class="main-content" id="dynamical-user">

    <!-- Header -->
    <div class="users-page-header">
        <h1 class="users-page-title">Gestion des Utilisateurs</h1>
        <p class="users-page-subtitle">Gérez les membres de votre organisation</p>
    </div>

    <!-- Stats utilisateurs -->
    <div class="row mb-4 g-3">
        <div class="col">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-users"></i></div>
                <div class="stat-number" aria-label="Utilisateurs actifs : <?php echo $totalActiveUsers; ?>"><?php echo $totalActiveUsers; ?></div>
                <div class="stat-label">Comptes actifs</div>
            </div>
        </div>
        <?php foreach ($userCountByRole as $row) : ?>
        <div class="col">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-user-tag"></i></div>
                <div class="stat-number"><?php echo $row['cnt']; ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($row['role_name'] ?? '—'); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Main layout -->
    <div class="users-layout">

        <!-- LEFT: recherche + liste -->
        <div class="users-left">
            <div class="users-search-bar">
                <div class="users-search-input-wrapper">
                    <label for="usersSearchInput" class="visually-hidden">Rechercher un utilisateur</label>
                    <i class="fas fa-search users-search-icon" aria-hidden="true"></i>
                    <input type="search" id="usersSearchInput" class="users-search-input"
                           placeholder="Rechercher un utilisateur..."
                           aria-label="Rechercher un utilisateur" />
                </div>
            </div>

            <div class="users-cards-grid users-list" id="users-list-container">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $initials  = strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1));
                        $fullName  = htmlspecialchars($user['firstname'] . ' ' . $user['lastname']);
                        $email     = htmlspecialchars($user['email']);
                        $roleName  = htmlspecialchars($user['role_name'] ?? '');
                        $roleClass = !empty($user['role_name']) ? userRoleBadgeClass($user['role_name']) : '';
                        ?>
                        <div class="user-card"
                             data-userid="<?= htmlspecialchars($user['id']) ?>"
                             data-userfirstname="<?= htmlspecialchars($user['firstname']) ?>"
                             data-userlastname="<?= htmlspecialchars($user['lastname']) ?>"
                             data-email="<?= $email ?>"
                             data-roleid="<?= htmlspecialchars($user['role_id'] ?? '') ?>"
                             data-name="<?= htmlspecialchars(strtolower($user['firstname'] . ' ' . $user['lastname'])) ?>">

                            <div class="user-card-avatar" aria-hidden="true"><?= $initials ?></div>

                            <div class="user-card-name" title="<?= $fullName ?>"><?= $fullName ?></div>

                            <?php if ($roleName): ?>
                                <span class="member-role-badge <?= $roleClass ?>"><?= $roleName ?></span>
                            <?php endif; ?>

                            <div class="user-card-email" title="<?= $email ?>">
                                <i class="fas fa-envelope" aria-hidden="true"></i> <?= $email ?>
                            </div>

                            <div class="user-card-actions">
                                <button class="user-icon-btn btn-view view-user"
                                        aria-label="Voir <?= $fullName ?>">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                                <button class="user-icon-btn btn-edit edit-user"
                                        aria-label="Modifier <?= $fullName ?>">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                                <button class="user-icon-btn btn-delete delete-user"
                                        aria-label="Supprimer <?= $fullName ?>">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="users-empty">
                        <i class="fas fa-users-slash" aria-hidden="true"></i>
                        <p>Aucun utilisateur trouvé. Ajoutez-en un pour commencer !</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: formulaire ajout / édition (partiel) -->
        <?php include __DIR__ . '/_form_add_user.php'; ?>

    </div>
</div>

<!-- Modal : détails utilisateur -->
<div class="users-detail-overlay" id="userDetailOverlay" role="dialog" aria-modal="true"
     aria-labelledby="userDetailModalTitle" style="display:none;">
    <div class="users-detail-modal">
        <button class="users-detail-close" id="closeUserDetail" aria-label="Fermer">&times;</button>
        <div class="users-detail-avatar" id="detailAvatar"></div>
        <h3 class="users-detail-name" id="detailName"></h3>
        <div class="users-detail-email" id="detailEmail">
            <i class="fas fa-envelope" aria-hidden="true"></i>
            <span id="detailEmailText"></span>
        </div>
        <div id="detailRoleBadge"></div>
        <div id="detailJobtitle" class="text-muted small mt-2" style="display:none;">
            <i class="fas fa-briefcase me-1" aria-hidden="true"></i>
            <span id="detailJobtitleText"></span>
        </div>
        <div id="detailFieldofwork" class="text-muted small mt-1" style="display:none;">
            <i class="fas fa-layer-group me-1" aria-hidden="true"></i>
            <span id="detailFieldofworkText"></span>
        </div>
        <div id="detailDegree" class="mt-2" style="display:none;">
            <div class="text-muted small mb-1"><i class="fas fa-graduation-cap me-1" aria-hidden="true"></i>Diplômes</div>
            <div id="detailDegreeList" class="d-flex flex-wrap gap-1"></div>
        </div>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?>">
document.addEventListener('DOMContentLoaded', function () {
    // Recherche live
    const searchInput = document.getElementById('usersSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.user-card').forEach(function (card) {
                const name = (card.dataset.name || '').toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    // Fermeture du modal "Voir"
    const overlay = document.getElementById('userDetailOverlay');
    document.getElementById('closeUserDetail').addEventListener('click', function () {
        overlay.style.display = 'none';
    });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.style.display = 'none';
    });
});
</script>

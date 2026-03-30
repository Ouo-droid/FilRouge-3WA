<?php
$userRole = $userRole ?? 'USER';
$canManageUsers = in_array($userRole, ['ADMIN', 'PDG'], true);

function teamRoleBadgeClass(string $role): string
{
    $r = strtolower($role);
    if (str_contains($r, 'admin'))                                                            return 'role-admin';
    if (str_contains($r, 'pdg'))                                                              return 'role-pdg';
    if (str_contains($r, 'cdp') || str_contains($r, 'chef') || str_contains($r, 'project')) return 'role-cdp';
    if (str_contains($r, 'dev'))                                                              return 'role-dev';
    if (str_contains($r, 'design'))                                                           return 'role-designer';
    return 'role-default';
}

function teamRoleBarClass(string $role): string
{
    $r = strtolower($role);
    if (str_contains($r, 'admin'))                                                            return 'bar-admin';
    if (str_contains($r, 'pdg'))                                                              return 'bar-pdg';
    if (str_contains($r, 'cdp') || str_contains($r, 'chef') || str_contains($r, 'project')) return 'bar-cdp';
    if (str_contains($r, 'dev'))                                                              return 'bar-dev';
    if (str_contains($r, 'design'))                                                           return 'bar-designer';
    return 'bar-default';
}

function teamFormatDate(string $dateStr): string
{
    try {
        $date = new DateTime($dateStr);
        $now  = new DateTime();
        $diff = $now->getTimestamp() - $date->getTimestamp();
        if ($diff < 3600)   return 'Il y a ' . round($diff / 60) . ' min';
        if ($diff < 86400)  return 'Il y a ' . round($diff / 3600) . ' heure' . (round($diff / 3600) > 1 ? 's' : '');
        if ($diff < 172800) return 'Hier';
        return $date->format('d/m/Y');
    } catch (\Exception $e) {
        return '';
    }
}
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="team-page-header">
        <h1 class="team-page-title">Gestion d'équipe</h1>
        <p class="team-page-subtitle">Gérez les membres de votre équipe et leurs projets</p>
    </div>

    <!-- Stats Cards -->
    <div class="team-stats-grid" role="region" aria-label="Statistiques de l'équipe">
        <div class="team-stat-card">
            <div class="team-stat-top">
                <span class="team-stat-label">Membres</span>
                <i class="fas fa-user-plus team-stat-icon icon-blue" aria-hidden="true"></i>
            </div>
            <div class="team-stat-value" id="stat-members" aria-label="Nombre de membres : <?= $totalMembers ?>"><?= $totalMembers ?></div>
        </div>
        <div class="team-stat-card">
            <div class="team-stat-top">
                <span class="team-stat-label">Tâches<br>actives</span>
                <i class="fas fa-calendar team-stat-icon icon-orange" aria-hidden="true"></i>
            </div>
            <div class="team-stat-value" id="stat-active" aria-label="Tâches actives : <?= $totalActiveTasks ?>"><?= $totalActiveTasks ?></div>
        </div>
        <div class="team-stat-card">
            <div class="team-stat-top">
                <span class="team-stat-label">Tâches<br>terminées</span>
                <i class="fas fa-award team-stat-icon icon-green" aria-hidden="true"></i>
            </div>
            <div class="team-stat-value" id="stat-done" aria-label="Tâches terminées : <?= $totalDoneTasks ?>"><?= $totalDoneTasks ?></div>
        </div>
        <div class="team-stat-card">
            <div class="team-stat-top">
                <span class="team-stat-label">Taux de<br>complétion</span>
                <i class="fas fa-award team-stat-icon icon-purple" aria-hidden="true"></i>
            </div>
            <div class="team-stat-value" id="stat-rate" aria-label="Taux de complétion : <?= $completionRate ?>%"><?= $completionRate ?>%</div>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="team-layout">

        <!-- LEFT: Members list -->
        <div class="team-left">
            <div class="team-search-bar">
                <div class="team-search-input-wrapper">
                    <label for="teamSearchInput" class="visually-hidden">Rechercher un membre</label>
                    <i class="fas fa-search team-search-icon" aria-hidden="true"></i>
                    <input type="search" id="teamSearchInput" class="team-search-input" placeholder="Rechercher un membre..." aria-label="Rechercher un membre de l'équipe" />
                </div>
                <?php if ($canManageUsers) : ?>
                <a href="/users" class="team-add-btn">
                    <i class="fas fa-plus" aria-hidden="true"></i> Ajouter
                </a>
                <?php endif; ?>
            </div>

            <div class="team-members-list" id="teamMembersList">
                <?php foreach ($members as $member): ?>
                    <?php
                    $initials     = strtoupper(substr($member['firstname'], 0, 1) . substr($member['lastname'], 0, 1));
                    $roleClass    = teamRoleBadgeClass($member['roleName']);
                    $projectCount = count($member['projects']);
                    $jobtitle     = htmlspecialchars($member['jobtitle'] ?? '');
                    ?>
                    <div class="team-member-card"
                         data-name="<?= htmlspecialchars(strtolower($member['firstname'] . ' ' . $member['lastname'])) ?>"
                         data-member-id="<?= htmlspecialchars($member['id']) ?>"
                         data-absent="<?= $member['isAbsent'] ? 'true' : 'false' ?>">

                        <!-- Section 1 : Identité -->
                        <div class="member-col-identity">
                            <div class="member-avatar"><?= $initials ?></div>
                            <div>
                                <div class="member-name">
                                    <?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>
                                    <?php if ($member['isAbsent']) : ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem;" title="Absence en cours">
                                        <i class="fas fa-user-clock"></i> Absent
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="member-email">
                                    <i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($member['email']) ?>
                                </div>
                                <span class="member-role-badge <?= $roleClass ?>"><?= htmlspecialchars($member['roleName']) ?></span>
                            </div>
                        </div>

                        <!-- Section 2 : Stats -->
                        <div class="member-col-stats">
                            <div class="member-stat">
                                <div class="member-stat-label">Tâches actives</div>
                                <div class="member-stat-value member-active-tasks"><?= $member['activeTasks'] ?></div>
                            </div>
                            <div class="member-stat">
                                <div class="member-stat-label">Tâches terminées</div>
                                <div class="member-stat-value member-done-tasks"><?= $member['doneTasks'] ?></div>
                            </div>
                            <div class="member-stat">
                                <div class="member-stat-label">Projets</div>
                                <div class="member-stat-value member-project-count"><?= $projectCount ?></div>
                            </div>
                        </div>

                        <!-- Section 3 : Projets assignés -->
                        <div class="member-col-projects">
                            <?php if (!empty($member['projects'])): ?>
                                <span class="member-projects-label">Projets assignés :</span>
                                <div class="member-projects-tags">
                                    <?php foreach ($member['projects'] as $projectName): ?>
                                        <span class="project-tag"><?= htmlspecialchars($projectName) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="member-projects-label" style="color:#cbd5e1;">Aucun projet</span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions (kebab) -->
                        <div class="member-kebab">
                            <button class="btn-kebab"
                                    type="button"
                                    aria-haspopup="menu"
                                    aria-expanded="false"
                                    aria-label="Actions pour <?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>"
                                    data-member-id="<?= htmlspecialchars($member['id']) ?>"
                                    data-member-name="<?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>">
                                <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                            </button>
                            <ul class="member-kebab-menu" role="menu" hidden>
                                <li role="none">
                                    <button class="kebab-item kebab-view" type="button" role="menuitem"
                                            data-member-id="<?= htmlspecialchars($member['id']) ?>"
                                            data-member-firstname="<?= htmlspecialchars($member['firstname']) ?>"
                                            data-member-lastname="<?= htmlspecialchars($member['lastname']) ?>"
                                            data-member-email="<?= htmlspecialchars($member['email']) ?>"
                                            data-member-role="<?= htmlspecialchars($member['roleName'] ?? '') ?>"
                                            data-member-jobtitle="<?= htmlspecialchars($member['jobtitle'] ?? '') ?>"
                                            data-member-fieldofwork="<?= htmlspecialchars($member['fieldofwork'] ?? '') ?>">
                                        <i class="fas fa-eye me-2" aria-hidden="true"></i>Voir le profil
                                    </button>
                                </li>
                                <?php if ($canManageUsers) : ?>
                                <li role="none">
                                    <button class="kebab-item kebab-absence" type="button" role="menuitem"
                                            data-member-id="<?= htmlspecialchars($member['id']) ?>"
                                            data-member-name="<?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>">
                                        <i class="fas fa-calendar-times me-2" aria-hidden="true"></i>Déclarer une absence
                                    </button>
                                </li>
                                <li role="none">
                                    <a class="kebab-item" role="menuitem" href="/users">
                                        <i class="fas fa-edit me-2" aria-hidden="true"></i>Modifier
                                    </a>
                                </li>
                                <li role="none"><div class="kebab-divider" role="separator"></div></li>
                                <li role="none">
                                    <button class="kebab-item kebab-danger kebab-delete" type="button" role="menuitem"
                                            data-member-id="<?= htmlspecialchars($member['id']) ?>"
                                            data-member-name="<?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>">
                                        <i class="fas fa-trash me-2" aria-hidden="true"></i>Supprimer
                                    </button>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                    </div>
                <?php endforeach; ?>

                <?php if (empty($members)): ?>
                    <div class="team-empty">
                        <i class="fas fa-users"></i>
                        <p>Aucun membre dans l'équipe.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Panels -->
        <div class="team-right">

            <?php /*
            <!-- Top Performers -->
            <div class="team-panel" role="region" aria-label="Top Performers">
                <div class="team-panel-title">
                    <i class="fas fa-award icon-yellow" aria-hidden="true"></i>
                    <span lang="en">Top Performers</span>
                </div>
                <div class="top-performers-list" id="topPerformersList">
                    <?php foreach ($topPerformers as $rank => $performer): ?>
                        <?php $initials = strtoupper(substr($performer['firstname'], 0, 1) . substr($performer['lastname'], 0, 1)); ?>
                        <div class="top-performer-item">
                            <span class="performer-rank rank-<?= $rank + 1 ?>"><?= $rank + 1 ?></span>
                            <div class="performer-avatar"><?= $initials ?></div>
                            <div class="performer-info">
                                <div class="performer-name"><?= htmlspecialchars($performer['firstname'] . ' ' . $performer['lastname']) ?></div>
                                <div class="performer-tasks"><?= $performer['doneTasks'] ?> tâches</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($topPerformers)): ?>
                        <p class="text-muted small">Aucune donnée disponible.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Role Distribution -->
            <div class="team-panel" role="region" aria-label="Répartition des rôles">
                <div class="team-panel-title">Répartition des rôles</div>
                <div class="role-distribution" id="roleDistribution">
                    <?php foreach ($roleDistribution as $role => $count): ?>
                        <?php
                        $barClass = teamRoleBarClass($role);
                        $barWidth = $maxRoleCount > 0 ? round($count / $maxRoleCount * 100) : 0;
                        ?>
                        <div class="role-dist-item">
                            <div class="role-dist-header">
                                <span class="role-dist-name"><?= htmlspecialchars($role) ?></span>
                                <span class="role-dist-count"><?= $count ?></span>
                            </div>
                            <div class="role-dist-bar-bg">
                                <div class="role-dist-bar <?= $barClass ?>" style="width: <?= $barWidth ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($roleDistribution)): ?>
                        <p class="text-muted small">Aucune donnée disponible.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="team-panel" role="region" aria-label="Activité récente" aria-live="polite">
                <div class="team-panel-title">Activité récente</div>
                <div class="recent-activity" id="recentActivity">
                    <?php if (!empty($recentActivity)): ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-text">
                                    <?php if (!empty($activity['firstname'])): ?>
                                        <strong><?= htmlspecialchars($activity['firstname'] . ' ' . $activity['lastname']) ?></strong>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($activity['text'] ?? '') ?>
                                </div>
                                <?php if (!empty($activity['createdat'])): ?>
                                    <div class="activity-time"><?= teamFormatDate($activity['createdat']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Aucune activité récente.</p>
                    <?php endif; ?>
                </div>
            </div>
            */ ?>

        </div>
    </div>
</div>

<!-- ── Modal : Déclarer une absence ── -->
<div id="modal-absence" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-absence-title" style="display:none;">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="modal-absence-title" style="font-size:1rem;font-weight:600;margin:0;">Déclarer une absence</h3>
            <button type="button" id="modal-absence-close" class="btn-close" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
            <p id="absence-member-label" style="font-weight:500;margin-bottom:.75rem;"></p>
            <input type="hidden" id="absence-user-id">
            <div class="users-form-group">
                <label for="absence-reason">Motif</label>
                <input type="text" id="absence-reason" class="form-control" placeholder="Congé, maladie...">
            </div>
            <div class="row g-2" style="margin-top:.75rem;">
                <div class="col-6">
                    <label for="absence-start">Date de début *</label>
                    <input type="date" id="absence-start" class="form-control" required>
                </div>
                <div class="col-6">
                    <label for="absence-end">Date de fin *</label>
                    <input type="date" id="absence-end" class="form-control" required>
                </div>
            </div>
            <div id="absence-error"   style="color:var(--danger,#dc3545);font-size:.85rem;margin-top:.5rem;display:none;"></div>
            <div id="absence-success" style="color:var(--success,#28a745);font-size:.85rem;margin-top:.5rem;display:none;"></div>
        </div>
        <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end;padding:.75rem 1.25rem;">
            <button type="button" id="modal-absence-cancel" class="btn btn-secondary btn-sm">Annuler</button>
            <button type="button" id="absence-submit" class="btn btn-primary btn-sm">Enregistrer</button>
        </div>
    </div>
</div>

<!-- ── Modal : Profil membre ── -->
<div id="team-member-overlay" class="team-member-overlay" role="dialog" aria-modal="true"
     aria-labelledby="tm-modal-title" style="display:none;">
    <div class="team-member-modal">
        <button class="team-member-modal-close" id="tm-modal-close" aria-label="Fermer">&times;</button>
        <div class="tm-avatar" id="tm-avatar" aria-hidden="true"></div>
        <h2 class="tm-name" id="tm-modal-title"></h2>
        <div class="tm-email" id="tm-email">
            <i class="fas fa-envelope" aria-hidden="true"></i> <span id="tm-email-text"></span>
        </div>
        <div id="tm-role-badge"></div>
        <div class="tm-meta" id="tm-jobtitle" style="display:none;">
            <i class="fas fa-briefcase" aria-hidden="true"></i><span id="tm-jobtitle-text"></span>
        </div>
        <div class="tm-meta" id="tm-fieldofwork" style="display:none;">
            <i class="fas fa-layer-group" aria-hidden="true"></i><span id="tm-fieldofwork-text"></span>
        </div>
        <div id="tm-degree" style="display:none; margin-top:.5rem;">
            <div class="tm-meta mb-1"><i class="fas fa-graduation-cap" aria-hidden="true"></i>Diplômes</div>
            <div id="tm-degree-list" class="d-flex flex-wrap gap-1 justify-content-center"></div>
        </div>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?>">
document.addEventListener('DOMContentLoaded', function () {

    // ── Recherche live ───────────────────────────────────────────────────────
    const searchInput = document.getElementById('teamSearchInput');
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.team-member-card').forEach(card => {
            card.style.display = (card.dataset.name || '').includes(query) ? '' : 'none';
        });
    });

    // ── Focus trap ───────────────────────────────────────────────────────────
    const FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';

    function trapFocus(modal, e) {
        const items = [...modal.querySelectorAll(FOCUSABLE)];
        if (!items.length) return;
        const first = items[0];
        const last  = items[items.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function roleBadgeClass(role) {
        const r = role.toLowerCase();
        if (r.includes('admin'))                                          return 'role-admin';
        if (r.includes('pdg'))                                            return 'role-pdg';
        if (r.includes('cdp') || r.includes('chef') || r.includes('project')) return 'role-cdp';
        if (r.includes('dev'))                                            return 'role-dev';
        if (r.includes('design'))                                         return 'role-designer';
        return 'role-default';
    }

    function roleBarClass(role) {
        const r = role.toLowerCase();
        if (r.includes('admin'))                                          return 'bar-admin';
        if (r.includes('pdg'))                                            return 'bar-pdg';
        if (r.includes('cdp') || r.includes('chef') || r.includes('project')) return 'bar-cdp';
        if (r.includes('dev'))                                            return 'bar-dev';
        if (r.includes('design'))                                         return 'bar-designer';
        return 'bar-default';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const diff = Math.floor((Date.now() - date.getTime()) / 1000);
        if (diff < 3600)   return `Il y a ${Math.round(diff / 60)} min`;
        if (diff < 86400)  { const h = Math.round(diff / 3600); return `Il y a ${h} heure${h > 1 ? 's' : ''}`; }
        if (diff < 172800) return 'Hier';
        return date.toLocaleDateString('fr-FR');
    }

    function animateValue(el, newVal) {
        if (el && el.textContent !== String(newVal)) {
            el.classList.add('stat-updating');
            setTimeout(() => {
                el.textContent = newVal;
                el.classList.remove('stat-updating');
            }, 200);
        }
    }

    // ── Mise à jour du DOM ───────────────────────────────────────────────────
    function applyData(data) {
        // Stat cards
        animateValue(document.getElementById('stat-members'), data.totalMembers);
        animateValue(document.getElementById('stat-active'),  data.totalActiveTasks);
        animateValue(document.getElementById('stat-done'),    data.totalDoneTasks);
        animateValue(document.getElementById('stat-rate'),    data.completionRate + '%');

        // Per-member stats
        data.members.forEach(member => {
            const card = document.querySelector(`.team-member-card[data-member-id="${member.id}"]`);
            if (!card) return;
            const active = card.querySelector('.member-active-tasks');
            const done   = card.querySelector('.member-done-tasks');
            const projs  = card.querySelector('.member-project-count');
            if (active) active.textContent = member.activeTasks;
            if (done)   done.textContent   = member.doneTasks;
            if (projs)  projs.textContent  = member.projects.length;
        });

        // Top performers
        const perfList = document.getElementById('topPerformersList');
        if (perfList) {
            perfList.innerHTML = data.topPerformers.length
                ? data.topPerformers.map((p, i) => {
                    const initials = (p.firstname[0] + p.lastname[0]).toUpperCase();
                    return `<div class="top-performer-item">
                        <span class="performer-rank rank-${i + 1}">${i + 1}</span>
                        <div class="performer-avatar">${initials}</div>
                        <div class="performer-info">
                            <div class="performer-name">${p.firstname} ${p.lastname}</div>
                            <div class="performer-tasks">${p.doneTasks} tâches</div>
                        </div>
                    </div>`;
                }).join('')
                : '<p class="text-muted small">Aucune donnée disponible.</p>';
        }

        // Role distribution
        const roleDist = document.getElementById('roleDistribution');
        if (roleDist) {
            const roles = data.roleDistribution;
            const maxCount = Math.max(...Object.values(roles), 1);
            roleDist.innerHTML = Object.keys(roles).length
                ? Object.entries(roles).map(([role, count]) => {
                    const barClass = roleBarClass(role);
                    const width    = Math.round(count / maxCount * 100);
                    return `<div class="role-dist-item">
                        <div class="role-dist-header">
                            <span class="role-dist-name">${role}</span>
                            <span class="role-dist-count">${count}</span>
                        </div>
                        <div class="role-dist-bar-bg">
                            <div class="role-dist-bar ${barClass}" style="width:${width}%"></div>
                        </div>
                    </div>`;
                }).join('')
                : '<p class="text-muted small">Aucune donnée disponible.</p>';
        }

        // Recent activity
        const activityEl = document.getElementById('recentActivity');
        if (activityEl) {
            activityEl.innerHTML = data.recentActivity.length
                ? data.recentActivity.map(a => {
                    const name = a.firstname ? `<strong>${a.firstname} ${a.lastname}</strong> ` : '';
                    const time = a.createdat ? `<div class="activity-time">${formatDate(a.createdat)}</div>` : '';
                    return `<div class="activity-item">
                        <div class="activity-text">${name}${a.text || ''}</div>
                        ${time}
                    </div>`;
                }).join('')
                : '<p class="text-muted small">Aucune activité récente.</p>';
        }
    }

    // ── Polling toutes les 30s ───────────────────────────────────────────────
    function fetchStats() {
        fetch('/api/team/stats', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(json => { if (json.success) applyData(json.data); })
            .catch(err => console.warn('[Team] polling error:', err));
    }

    setInterval(fetchStats, 30000);

    // ── Kebab menu ───────────────────────────────────────────────────────────
    let openKebabMenu = null;

    function closeAllKebabs() {
        if (!openKebabMenu) return;
        openKebabMenu.hidden = true;
        const btn = openKebabMenu.previousElementSibling;
        if (btn) btn.setAttribute('aria-expanded', 'false');
        openKebabMenu = null;
    }

    document.addEventListener('click', function (e) {
        const kebabBtn = e.target.closest('.btn-kebab');
        if (kebabBtn) {
            e.stopPropagation();
            const menu = kebabBtn.nextElementSibling;
            const isOpen = !menu.hidden;
            closeAllKebabs();
            if (!isOpen) {
                menu.hidden = false;
                kebabBtn.setAttribute('aria-expanded', 'true');
                openKebabMenu = menu;
                // Focus first item for keyboard accessibility
                menu.querySelector('.kebab-item')?.focus();
            }
            return;
        }
        // Click outside → close
        if (!e.target.closest('.member-kebab-menu')) closeAllKebabs();
    });

    document.addEventListener('keydown', function (e) {
        if (!openKebabMenu) return;
        if (e.key === 'Escape') { closeAllKebabs(); return; }
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const items = [...openKebabMenu.querySelectorAll('.kebab-item')];
            const idx = items.indexOf(document.activeElement);
            const next = e.key === 'ArrowDown'
                ? items[(idx + 1) % items.length]
                : items[(idx - 1 + items.length) % items.length];
            next?.focus();
        }
    });

    // ── Profil membre modal ──────────────────────────────────────────────────
    const tmOverlay      = document.getElementById('team-member-overlay');
    const tmClose        = document.getElementById('tm-modal-close');
    const tmAvatar       = document.getElementById('tm-avatar');
    const tmName         = document.getElementById('tm-modal-title');
    const tmEmailText    = document.getElementById('tm-email-text');
    const tmRoleBadge    = document.getElementById('tm-role-badge');
    const tmJobtitleEl   = document.getElementById('tm-jobtitle');
    const tmJobtitleText = document.getElementById('tm-jobtitle-text');
    const tmFieldEl      = document.getElementById('tm-fieldofwork');
    const tmFieldText    = document.getElementById('tm-fieldofwork-text');
    const tmDegreeEl     = document.getElementById('tm-degree');
    const tmDegreeList   = document.getElementById('tm-degree-list');

    function openProfileModal(btn) {
        profileTrigger = btn;
        const id         = btn.dataset.memberId;
        const firstname  = btn.dataset.memberFirstname || '';
        const lastname   = btn.dataset.memberLastname  || '';
        const email      = btn.dataset.memberEmail     || '';
        const role       = btn.dataset.memberRole      || '';
        const jobtitle   = btn.dataset.memberJobtitle  || '';
        const fieldofwork= btn.dataset.memberFieldofwork || '';

        const initials = (firstname.charAt(0) + lastname.charAt(0)).toUpperCase();
        tmAvatar.textContent    = initials;
        tmName.textContent      = `${firstname} ${lastname}`;
        tmEmailText.textContent = email;

        // Role badge
        tmRoleBadge.innerHTML = role
            ? `<span class="member-role-badge ${roleBadgeClass(role)}" style="margin-bottom:.5rem;display:inline-block;">${role}</span>`
            : '';

        // Jobtitle
        if (jobtitle) {
            tmJobtitleText.textContent = jobtitle;
            tmJobtitleEl.style.display = 'block';
        } else {
            tmJobtitleEl.style.display = 'none';
        }

        // Fieldofwork
        if (fieldofwork) {
            tmFieldText.textContent = fieldofwork;
            tmFieldEl.style.display = 'block';
        } else {
            tmFieldEl.style.display = 'none';
        }

        // Degrees — load from API for complete data
        tmDegreeEl.style.display = 'none';
        tmDegreeList.innerHTML   = '';

        tmOverlay.style.display = 'flex';
        tmClose.focus();

        if (id) {
            fetch(`/api/user/${encodeURIComponent(id)}`)
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data?.user) return;
                    const u = data.user;
                    if (u.jobtitle && !jobtitle) {
                        tmJobtitleText.textContent = u.jobtitle;
                        tmJobtitleEl.style.display = 'block';
                    }
                    if (u.fieldofwork && !fieldofwork) {
                        tmFieldText.textContent = u.fieldofwork;
                        tmFieldEl.style.display = 'block';
                    }
                    if (u.degree?.length) {
                        tmDegreeList.innerHTML = u.degree
                            .map(d => `<span class="badge bg-light text-dark border">${d}</span>`)
                            .join('');
                        tmDegreeEl.style.display = 'block';
                    }
                })
                .catch(() => {});
        }
    }

    let profileTrigger = null;

    function closeProfileModal() {
        if (tmOverlay) tmOverlay.style.display = 'none';
        if (profileTrigger) { profileTrigger.focus(); profileTrigger = null; }
    }

    tmClose?.addEventListener('click', closeProfileModal);
    tmOverlay?.addEventListener('click', e => { if (e.target === tmOverlay) closeProfileModal(); });
    tmOverlay?.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeProfileModal(); return; }
        if (e.key === 'Tab')    trapFocus(tmOverlay, e);
    });

    // Delegation pour les actions kebab
    document.addEventListener('click', function (e) {
        // Voir le profil
        const viewBtn = e.target.closest('.kebab-view');
        if (viewBtn) { closeAllKebabs(); openProfileModal(viewBtn); return; }

        // Déclarer une absence
        const absenceBtn = e.target.closest('.kebab-absence');
        if (absenceBtn) {
            const kebabBtn = absenceBtn.closest('.member-kebab')?.querySelector('.btn-kebab');
            closeAllKebabs();
            openAbsenceModal(absenceBtn.dataset.memberId, absenceBtn.dataset.memberName, kebabBtn);
            return;
        }

        // Supprimer
        const deleteBtn = e.target.closest('.kebab-delete');
        if (deleteBtn) {
            closeAllKebabs();
            const memberId   = deleteBtn.dataset.memberId;
            const memberName = deleteBtn.dataset.memberName;
            if (!confirm(`Supprimer le membre "${memberName}" ? Cette action est irréversible.`)) return;

            fetch(`/api/delete/user/${encodeURIComponent(memberId)}`, { method: 'DELETE' })
                .then(r => r.json().catch(() => ({})))
                .then(data => {
                    if (data?.delete === 'true' || data?.success) {
                        const card = document.querySelector(`.team-member-card[data-member-id="${memberId}"]`);
                        if (card) card.remove();
                    } else {
                        alert(data?.error ?? 'Impossible de supprimer ce membre.');
                    }
                })
                .catch(() => alert('Erreur réseau. Veuillez réessayer.'));
        }
    });

    // ── Absence modal ────────────────────────────────────────────────────────
    const absenceModal   = document.getElementById('modal-absence');
    const absenceClose   = document.getElementById('modal-absence-close');
    const absenceCancel  = document.getElementById('modal-absence-cancel');
    const absenceSubmit  = document.getElementById('absence-submit');
    const absenceUserId  = document.getElementById('absence-user-id');
    const absenceLabel   = document.getElementById('absence-member-label');
    const absenceReason  = document.getElementById('absence-reason');
    const absenceStart   = document.getElementById('absence-start');
    const absenceEnd     = document.getElementById('absence-end');
    const absenceError   = document.getElementById('absence-error');
    const absenceSuccess = document.getElementById('absence-success');

    let absenceTrigger = null;

    function openAbsenceModal(memberId, memberName, trigger = null) {
        if (!absenceModal) return;
        absenceTrigger = trigger;
        absenceUserId.value  = memberId;
        absenceLabel.textContent = 'Membre : ' + memberName;
        absenceReason.value  = '';
        absenceStart.value   = '';
        absenceEnd.value     = '';
        absenceError.style.display   = 'none';
        absenceSuccess.style.display = 'none';
        absenceModal.style.display = 'flex';
        absenceStart.focus();
    }

    function closeAbsenceModal() {
        if (absenceModal) absenceModal.style.display = 'none';
        if (absenceTrigger) { absenceTrigger.focus(); absenceTrigger = null; }
    }

    absenceClose?.addEventListener('click', closeAbsenceModal);
    absenceCancel?.addEventListener('click', closeAbsenceModal);
    absenceModal?.addEventListener('click', e => { if (e.target === absenceModal) closeAbsenceModal(); });
    absenceModal?.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeAbsenceModal(); return; }
        if (e.key === 'Tab')    trapFocus(absenceModal, e);
    });

    absenceSubmit?.addEventListener('click', async () => {
        absenceError.style.display = 'none';
        absenceSuccess.style.display = 'none';

        const userId = absenceUserId.value.trim();
        const start  = absenceStart.value;
        const end    = absenceEnd.value;
        const reason = absenceReason.value.trim();

        if (!start) { absenceError.textContent = 'La date de début est obligatoire.'; absenceError.style.display = 'block'; return; }
        if (!end)   { absenceError.textContent = 'La date de fin est obligatoire.';   absenceError.style.display = 'block'; return; }
        if (end < start) { absenceError.textContent = 'La date de fin doit être après la date de début.'; absenceError.style.display = 'block'; return; }

        absenceSubmit.disabled = true;
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res = await fetch('/api/add/absence', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body   : JSON.stringify({ userId, reason: reason || null, startDate: start, endDate: end }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                absenceError.textContent = data.error ?? 'Erreur lors de l\'enregistrement.';
                absenceError.style.display = 'block';
            } else {
                absenceSuccess.textContent = 'Absence enregistrée.';
                absenceSuccess.style.display = 'block';
                // Update badge on the card
                const card = document.querySelector(`.team-member-card[data-member-id="${userId}"]`);
                if (card) {
                    card.dataset.absent = 'true';
                    const nameEl = card.querySelector('.member-name');
                    if (nameEl && !nameEl.querySelector('.absent-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-warning text-dark ms-1 absent-badge';
                        badge.style.fontSize = '.7rem';
                        badge.innerHTML = '<i class="fas fa-user-clock"></i> Absent';
                        nameEl.appendChild(badge);
                    }
                }
                setTimeout(closeAbsenceModal, 1500);
            }
        } catch {
            absenceError.textContent = 'Erreur réseau. Veuillez réessayer.';
            absenceError.style.display = 'block';
        } finally {
            absenceSubmit.disabled = false;
        }
    });
});
</script>

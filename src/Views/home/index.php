<div class="main-content">

    <?php
    $role = $role ?? 'USER';

    $dashboardTitles = [
        'PDG'   => ['title' => 'Dashboard Dirigeant',       'subtitle' => 'Vue stratégique de l\'activité'],
        'ADMIN' => ['title' => 'Dashboard Administrateur',  'subtitle' => 'Supervision globale de la plateforme'],
        'CDP'   => ['title' => 'Dashboard Chef de Projet',  'subtitle' => 'Suivi de vos projets et de votre équipe'],
        'USER'  => ['title' => 'Dashboard Utilisateur',     'subtitle' => 'Vos tâches et votre activité du jour'],
    ];
    $headerInfo = $dashboardTitles[$role] ?? $dashboardTitles['USER'];
    ?>

    <div class="users-page-header">
        <div>
            <h1 class="users-page-title"><?php echo $headerInfo['title']; ?></h1>
            <p class="users-page-subtitle"><?php echo $headerInfo['subtitle']; ?></p>
        </div>
        <?php if ($role === 'ADMIN') : ?>
        <a href="/users" class="btn-save">
            <i class="fas fa-users-cog me-2" aria-hidden="true"></i>Gérer les utilisateurs
        </a>
        <?php endif; ?>
    </div>

    <?php if ($role === 'PDG') :
        $kpis = $kpis ?? [];
        $tasksByState = $tasksByState ?? [];
    ?>

    <div class="row mb-4 g-3">
        <!-- KPI : Utilisateurs actifs -->
        <div class="col-6 col-md-3">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-users"></i></div>
                <div class="stat-number" aria-label="Utilisateurs actifs : <?php echo $kpis['totalUsers']; ?>"><?php echo $kpis['totalUsers']; ?></div>
                <div class="stat-label">Utilisateurs<br>actifs</div>
            </div>
        </div>
        <!-- KPI : Projets -->
        <div class="col-6 col-md-3">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-folder-open"></i></div>
                <div class="stat-number" aria-label="Projets : <?php echo $kpis['totalProjects']; ?>"><?php echo $kpis['totalProjects']; ?></div>
                <div class="stat-label">Projets<br>en cours</div>
            </div>
        </div>
        <!-- KPI : Taux complétion -->
        <div class="col-6 col-md-3">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-chart-pie"></i></div>
                <div class="stat-number" aria-label="Taux de complétion : <?php echo $kpis['completionRate']; ?>%"><?php echo $kpis['completionRate']; ?>%</div>
                <div class="stat-label">Taux de<br>complétion</div>
            </div>
        </div>
        <!-- KPI : Clients -->
        <div class="col-6 col-md-3">
            <div class="stat-card kpi-card">
                <div class="stat-icon" aria-hidden="true"><i class="fas fa-building"></i></div>
                <div class="stat-number" aria-label="Clients : <?php echo $kpis['totalClients']; ?>"><?php echo $kpis['totalClients']; ?></div>
                <div class="stat-label">Clients</div>
            </div>
        </div>
    </div>

    <!-- Alertes stratégiques -->
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-6">
            <div class="card settings-card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="fas fa-exclamation-triangle text-warning me-2" aria-hidden="true"></i>Alertes</h2>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded bg-alert-warning">
                        <div>
                            <div class="fw-semibold">Projets en retard</div>
                            <div class="text-muted small">Échéance théorique dépassée</div>
                        </div>
                        <span class="badge bg-danger fs-6"><?php echo $kpis['lateProjects']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded bg-alert-danger">
                        <div>
                            <div class="fw-semibold">Tâches haute priorité ouvertes</div>
                            <div class="text-muted small">Non terminées</div>
                        </div>
                        <span class="badge bg-warning text-dark fs-6"><?php echo $kpis['highPriorityOpen']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card settings-card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="fas fa-tasks me-2" aria-hidden="true"></i>Tâches par statut</h2>
                    <?php if (!empty($tasksByState)) : ?>
                        <?php foreach ($tasksByState as $row) : ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small"><?php echo htmlspecialchars($row['state_name'] ?? 'Sans statut'); ?></span>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:100px;height:8px;">
                                        <?php
                                        $pct = $kpis['totalTasks'] > 0 ? round((int)$row['cnt'] / $kpis['totalTasks'] * 100) : 0;
                                        ?>
                                        <div class="progress-bar" role="progressbar" style="width:<?php echo $pct; ?>%;background:#6366f1;" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="fw-semibold small"><?php echo $row['cnt']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-muted small">Aucune donnée disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides PDG -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card settings-card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="fas fa-rocket me-2" aria-hidden="true"></i>Accès rapide</h2>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/projects" class="btn btn-outline-primary"><i class="fas fa-folder me-1" aria-hidden="true"></i>Tous les projets</a>
                        <a href="/tasks" class="btn btn-outline-primary"><i class="fas fa-tasks me-1" aria-hidden="true"></i>Toutes les tâches</a>
                        <a href="/clients" class="btn btn-outline-primary"><i class="fas fa-building me-1" aria-hidden="true"></i>Clients</a>
                        <a href="/team" class="btn btn-outline-primary"><i class="fas fa-users me-1" aria-hidden="true"></i>Équipe</a>
                        <a href="/users" class="btn btn-outline-secondary"><i class="fas fa-user-cog me-1" aria-hidden="true"></i>Utilisateurs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN — Gestion des comptes
    // ─────────────────────────────────────────────────────────────────────────
    elseif ($role === 'ADMIN') :
        $recentUsers     = $recentUsers ?? [];
        $userCountByRole = $userCountByRole ?? [];
        $totalActiveUsers = $totalActiveUsers ?? 0;

        function roleBadgeClass(string $role): string {
            return match($role) {
                'PDG'   => 'bg-purple',
                'ADMIN' => 'bg-danger',
                'CDP'   => 'bg-warning text-dark',
                'USER'  => 'bg-primary',
                default => 'bg-secondary',
            };
        }
    ?>

    <!-- KPI bar -->
    <div class="admin-kpi-bar" role="region" aria-label="Statistiques utilisateurs">
        <div class="admin-kpi-card admin-kpi-total">
            <div class="admin-kpi-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
            <div class="admin-kpi-body">
                <div class="admin-kpi-value" aria-label="Total comptes actifs : <?php echo $totalActiveUsers; ?>"><?php echo $totalActiveUsers; ?></div>
                <div class="admin-kpi-label">Comptes actifs</div>
            </div>
        </div>
        <?php
        $roleColors = [
            'PDG'   => ['icon' => 'fas fa-crown',        'color' => '#7c3aed'],
            'ADMIN' => ['icon' => 'fas fa-shield-alt',   'color' => '#dc2626'],
            'CDP'   => ['icon' => 'fas fa-project-diagram','color' => '#d97706'],
            'USER'  => ['icon' => 'fas fa-user',         'color' => '#2563eb'],
        ];
        foreach ($userCountByRole as $row) :
            $rName = $row['role_name'] ?? '—';
            $cfg   = $roleColors[$rName] ?? ['icon' => 'fas fa-user-tag', 'color' => '#64748b'];
        ?>
        <div class="admin-kpi-card" style="--kpi-accent:<?php echo $cfg['color']; ?>">
            <div class="admin-kpi-icon"><i class="<?php echo $cfg['icon']; ?>" aria-hidden="true"></i></div>
            <div class="admin-kpi-body">
                <div class="admin-kpi-value"><?php echo $row['cnt']; ?></div>
                <div class="admin-kpi-label"><?php echo htmlspecialchars($rName); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tableau comptes récents -->
    <div class="card settings-card">
        <div class="card-body">
            <h2 class="h5 mb-3"><i class="fas fa-user-clock me-2" aria-hidden="true"></i>Comptes récents</h2>
            <?php if (!empty($recentUsers)) : ?>
            <div class="table-responsive">
                <table class="table admin-users-table" aria-label="Comptes utilisateurs récents">
                    <thead>
                        <tr>
                            <th scope="col">Utilisateur</th>
                            <th scope="col">Email</th>
                            <th scope="col">Rôle</th>
                            <th scope="col">Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentUsers as $u) :
                        $initials = strtoupper(substr($u['firstname'] ?? '', 0, 1) . substr($u['lastname'] ?? '', 0, 1));
                        $rName    = $u['role_name'] ?? '—';
                        $cfg      = $roleColors[$rName] ?? ['icon' => 'fas fa-user-tag', 'color' => '#64748b'];
                    ?>
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <div class="admin-user-avatar" style="background:<?php echo $cfg['color']; ?>20;color:<?php echo $cfg['color']; ?>">
                                        <?php echo $initials; ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($u['firstname'] . ' ' . $u['lastname']); ?></strong>
                                </div>
                            </td>
                            <td class="text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="admin-role-badge" style="background:<?php echo $cfg['color']; ?>18;color:<?php echo $cfg['color']; ?>">
                                    <i class="<?php echo $cfg['icon']; ?> me-1" aria-hidden="true"></i>
                                    <?php echo htmlspecialchars($rName); ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo !empty($u['createdat']) ? (new DateTime($u['createdat']))->format('d/m/Y') : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else : ?>
                <p class="text-muted">Aucun utilisateur actif.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // ─────────────────────────────────────────────────────────────────────────
    // CDP — Mes projets + tâches urgentes
    // ─────────────────────────────────────────────────────────────────────────
    elseif ($role === 'CDP') :
        $myProjects          = $myProjects ?? [];
        $urgentTasks         = $urgentTasks ?? [];
        $projectsCount       = $projectsCount ?? 0;
        $completedTasksCount = $completedTasksCount ?? 0;
        $totalTasksCount     = $totalTasksCount ?? 0;
    ?>

    <!-- Stats CDP -->
    <div class="cdp-stats-row mb-4" role="region" aria-label="Statistiques chef de projet">
        <div class="cdp-stat-card">
            <div class="cdp-stat-icon"><i class="fas fa-folder-open"></i></div>
            <span class="cdp-stat-value"><?php echo $projectsCount; ?></span>
            <span class="cdp-stat-label">Mes projets</span>
        </div>
        <div class="cdp-stat-card">
            <div class="cdp-stat-icon"><i class="fas fa-tasks"></i></div>
            <span class="cdp-stat-value"><?php echo $totalTasksCount; ?></span>
            <span class="cdp-stat-label">Tâches totales</span>
        </div>
        <div class="cdp-stat-card">
            <div class="cdp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <span class="cdp-stat-value"><?php echo $completedTasksCount; ?></span>
            <span class="cdp-stat-label">Tâches terminées</span>
        </div>
    </div>

    <div class="row g-3">
        <!-- Mes projets -->
        <div class="col-12 col-lg-7">
            <div class="card settings-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0"><i class="fas fa-folder me-2" aria-hidden="true"></i>Mes projets</h2>
                        <a href="/projects" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1" aria-hidden="true"></i>Gérer</a>
                    </div>
                    <?php if (!empty($myProjects)) : ?>
                        <?php foreach ($myProjects as $p) :
                            $status = 'En cours';
                            $badgeCls = 'bg-primary';
                            if (!empty($p['realDeadLine'])) { $status = 'Terminé'; $badgeCls = 'bg-success'; }
                            elseif (!empty($p['theoricalDeadLine']) && new DateTime($p['theoricalDeadLine']) < new DateTime()) { $status = 'En retard'; $badgeCls = 'bg-danger'; }
                        ?>
                        <div class="d-flex align-items-center justify-content-between mb-3 p-2 rounded bg-gray-50">
                            <div class="cdp-project-item-inner">
                                <div class="fw-semibold text-truncate"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div class="progress" style="width:80px;height:6px;">
                                        <div class="progress-bar" style="width:<?php echo $p['progress']; ?>%;background:#6366f1;" role="progressbar" aria-valuenow="<?php echo $p['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-muted small"><?php echo $p['progress']; ?>%</span>
                                </div>
                            </div>
                            <div class="ms-3 d-flex align-items-center gap-2">
                                <span class="badge <?php echo $badgeCls; ?>"><?php echo $status; ?></span>
                                <a href="/project/<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-secondary py-0">Voir</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-muted small">Aucun projet assigné. <a href="/projects">Créer un projet</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tâches à assigner -->
        <div class="col-12 col-lg-5">
            <div class="card settings-card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><i class="fas fa-user-plus text-primary me-2" aria-hidden="true"></i>Tâches à assigner</h2>
                    <?php $unassignedTasks = $unassignedTasks ?? []; ?>
                    <?php if (!empty($unassignedTasks)) : ?>
                        <?php foreach ($unassignedTasks as $t) : ?>
                        <div class="mb-2 p-2 rounded bg-alert-warning d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-semibold small text-truncate"><?php echo htmlspecialchars($t['name']); ?></span>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <?php if (!empty($t['theoreticalenddate'])) : ?>
                                    <span class="text-muted small"><i class="fas fa-calendar me-1" aria-hidden="true"></i><?php echo (new DateTime($t['theoreticalenddate']))->format('d/m/Y'); ?></span>
                                <?php endif; ?>
                                <button class="btn btn-primary btn-sm py-0 px-2 cdp-assign-btn"
                                    data-task-id="<?php echo htmlspecialchars($t['id']); ?>"
                                    data-task-name="<?php echo htmlspecialchars($t['name']); ?>"
                                    data-task-desc="<?php echo htmlspecialchars($t['description'] ?? ''); ?>"
                                    data-task-project="<?php echo htmlspecialchars($t['project_name'] ?? ''); ?>"
                                    data-task-deadline="<?php echo !empty($t['theoreticalenddate']) ? (new DateTime($t['theoreticalenddate']))->format('d/m/Y') : ''; ?>"
                                    data-task-effort="<?php echo htmlspecialchars((string)($t['effortrequired'] ?? '')); ?>"
                                    data-task-priority="<?php echo htmlspecialchars($t['priority'] ?? ''); ?>"
                                    style="font-size:.75rem;">
                                    <i class="fas fa-user-plus me-1" aria-hidden="true"></i>Assigner
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="text-muted small">Toutes les tâches sont assignées.</p>
                    <?php endif; ?>
                    <a href="/tasks" class="btn btn-outline-primary btn-sm mt-2 w-100">Voir toutes les tâches</a>
                </div>
            </div>
        </div>
    </div>

<!-- ── Modal assignation tâche (CDP) ── -->
<div id="cdp-assign-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="cdp-assign-title" style="display:none;">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <h3 id="cdp-assign-title" style="font-size:1rem;font-weight:600;margin:0;">Assigner une tâche</h3>
            <button type="button" class="btn-close" id="cdp-assign-close" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
            <!-- Infos tâche -->
            <div class="cdp-assign-info mb-3">
                <div class="fw-semibold mb-1" id="cdp-assign-task-name" style="font-size:1rem;color:#0f172a;"></div>
                <div class="text-muted small mb-2" id="cdp-assign-task-project"></div>
                <div id="cdp-assign-task-desc" class="small mb-3" style="color:#475569;"></div>
                <div class="d-flex gap-3 flex-wrap">
                    <span class="small text-muted" id="cdp-assign-task-deadline" style="display:none;"><i class="fas fa-calendar me-1"></i><span class="val"></span></span>
                    <span class="small text-muted" id="cdp-assign-task-effort" style="display:none;"><i class="fas fa-stopwatch me-1"></i><span class="val"></span>h requises</span>
                    <span id="cdp-assign-task-priority" style="display:none;"></span>
                </div>
            </div>
            <hr style="border-color:#f1f5f9;margin:1rem 0;">
            <!-- Recherche utilisateur -->
            <label class="small fw-semibold text-muted mb-1 d-block" for="cdp-assign-search">Assigner à</label>
            <input type="text" id="cdp-assign-search" class="form-control form-control-sm mb-1" placeholder="Rechercher un utilisateur..." autocomplete="off">
            <div id="cdp-assign-results" style="border:1px solid #e2e8f0;border-radius:8px;max-height:180px;overflow-y:auto;display:none;"></div>
            <div id="cdp-assign-selected" class="mt-2 d-flex align-items-center gap-2" style="display:none!important;">
                <i class="fas fa-user-check text-success"></i>
                <span id="cdp-assign-selected-name" class="small fw-semibold"></span>
                <button type="button" id="cdp-assign-clear" class="btn btn-link btn-sm p-0 text-danger ms-auto" style="font-size:.75rem;">Changer</button>
            </div>
            <div id="cdp-assign-error" style="color:#dc2626;font-size:.82rem;margin-top:.5rem;display:none;"></div>
        </div>
        <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end;padding:.75rem 1.25rem;">
            <button type="button" class="btn-cancel" id="cdp-assign-cancel">Annuler</button>
            <button type="button" class="btn btn-primary btn-sm" id="cdp-assign-submit" disabled>Assigner</button>
        </div>
    </div>
</div>

    <?php
    // ─────────────────────────────────────────────────────────────────────────
    // USER / Collaborateur — Mes tâches du jour
    // ─────────────────────────────────────────────────────────────────────────
    else :
        $tasks               = $tasks ?? [];
        $completedTasksCount = $completedTasksCount ?? 0;
        $projectsCount       = $projectsCount ?? 0;
        $weeklyActivity      = $weeklyActivity ?? 0;
        $monthsFr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $currentMonthIdx = (int) date('n') - 1;
        $currentYear = date('Y');
    ?>

    <!-- Calendar Widget -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="calendar-widget">
                <div class="calendar-header">
                    <button class="btn btn-sm btn-link text-muted" id="prev-week" aria-label="Semaine précédente">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <h2 class="calendar-title" id="calendar-month-year" style="font-size:1rem;font-weight:600;">
                        <span><?php echo $monthsFr[$currentMonthIdx] . ' ' . $currentYear; ?></span>
                        <label for="calendar-datepicker" class="visually-hidden">Sélectionner une date</label>
                        <input type="date" id="calendar-datepicker" class="datepicker-input" value="<?php echo date('Y-m-d'); ?>" aria-label="Sélectionner une date dans le calendrier">
                    </h2>
                    <button class="btn btn-sm btn-link text-muted" id="next-week" aria-label="Semaine suivante">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="calendar-grid" id="calendar-days-grid">
                    <?php
                    $dayNamesFr   = ['D','L','M','M','J','V','S'];
                    $dayFullNames = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
                    $today        = new DateTime();
                    $startOfWeek  = clone $today;
                    $startOfWeek->modify('-' . $today->format('w') . ' days');
                    for ($i = 0; $i < 7; ++$i) {
                        $currentDay = clone $startOfWeek;
                        $currentDay->modify('+' . $i . ' days');
                        $isActive = ($currentDay->format('Y-m-d') === $today->format('Y-m-d')) ? 'active' : '';
                        echo '<div class="calendar-day-col ' . $isActive . '">';
                        echo '<div class="calendar-day-name" aria-label="' . $dayFullNames[$i] . '">' . $dayNamesFr[$i] . '</div>';
                        echo '<div class="calendar-day-num">' . $currentDay->format('j') . '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="tasks-table">
        <div class="table-header">
            <h2 class="mb-0" style="font-size:1.1rem;font-weight:600;">Liste des tâches du jour</h2>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-add-task">Saisir une tâche réelle +</button>
                <button type="button" id="btn-request-absence"
                        class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                        aria-haspopup="dialog">
                    <i class="fas fa-calendar-times" aria-hidden="true"></i> Demander une absence
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table" aria-label="Liste des tâches du jour">
                <thead>
                <tr>
                    <th scope="col">Intitulé de la tâche</th>
                    <th scope="col">Client</th>
                    <th scope="col">Chef de projet</th>
                    <th scope="col">Date d'échéance</th>
                    <th scope="col">Statut</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tasks)) : ?>
                    <tr><td colspan="5" class="text-center text-muted">Aucune tâche assignée pour le moment.</td></tr>
                <?php else : ?>
                    <?php foreach ($tasks as $task) :
                        $stateName   = $task['state_name'] ?? '';
                        $statusLabel = $stateName ?: 'À faire';
                        $badgeClass  = 'bg-secondary';
                        if (false !== stripos($stateName, 'termin') || false !== stripos($stateName, 'done') || false !== stripos($stateName, 'clos')) {
                            $badgeClass = 'bg-success';
                        } elseif (false !== stripos($stateName, 'cours') || false !== stripos($stateName, 'progress')) {
                            $badgeClass = 'bg-warning';
                        } elseif (false !== stripos($stateName, 'faire') || false !== stripos($stateName, 'todo') || false !== stripos($stateName, 'backlog')) {
                            $badgeClass = 'bg-danger';
                        }
                    ?>
                    <tr <?php if (!empty($task['pm_absent'])) echo 'class="table-warning"'; ?>>
                        <td><strong><?php echo htmlspecialchars($task['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($task['pm_name']); ?>
                            <?php if (!empty($task['pm_absent'])) : ?>
                                <span class="badge bg-warning text-dark ms-1" title="Chef de projet absent aujourd'hui">
                                    <i class="fas fa-user-clock me-1" aria-hidden="true"></i>Absent
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($task['theoricalenddate']) ? (new DateTime($task['theoricalenddate']))->format('d/m/Y') : 'N/A'; ?></td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal saisie effort réel -->
    <div id="effort-modal" class="form-overlay" role="dialog" aria-modal="true" aria-labelledby="effort-modal-title" style="display:none;">
        <div class="edit-form" style="max-width:440px;">
            <div class="form-header">
                <h3 id="effort-modal-title"><i class="fas fa-clock me-2" aria-hidden="true"></i>Saisir l'effort réel</h3>
                <button type="button" id="effort-modal-close" class="btn-close" aria-label="Fermer">×</button>
            </div>
            <div class="form-content">
                <div class="form-group">
                    <label for="effort-task-select">Tâche</label>
                    <select id="effort-task-select">
                        <option value="">— Sélectionner une tâche —</option>
                        <?php foreach ($tasks as $task) : ?>
                            <?php if (!empty($task['id'])) : ?>
                            <option value="<?php echo htmlspecialchars((string)$task['id']); ?>"
                                    data-effort="<?php echo htmlspecialchars((string)($task['effortmade'] ?? '')); ?>">
                                <?php echo htmlspecialchars($task['name']); ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="effort-value">Effort réalisé (heures)</label>
                    <input type="number" id="effort-value" min="0.5" step="0.5" placeholder="ex: 3.5">
                </div>
                <div id="effort-error" class="alert alert-danger" style="display:none;" role="alert"></div>
                <div id="effort-success" class="alert alert-success" style="display:none;" role="status"></div>
            </div>
            <div class="form-actions">
                <button type="button" id="effort-modal-cancel" class="btn-cancel">Annuler</button>
                <button type="button" id="effort-modal-submit" class="btn-save">Enregistrer</button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards" role="region" aria-label="Statistiques personnelles">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></div>
            <div class="stat-number" aria-label="Activité hebdomadaire : <?php echo $weeklyActivity; ?>%"><?php echo $weeklyActivity; ?>%</div>
            <div class="stat-label">Activité<br>hebdomadaire</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"><i class="fas fa-tasks"></i></div>
            <div class="stat-number" aria-label="Tâches accomplies : <?php echo $completedTasksCount; ?>"><?php echo $completedTasksCount; ?></div>
            <div class="stat-label">Tâches<br>accomplies</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"><i class="fas fa-folder-open"></i></div>
            <div class="stat-number" aria-label="Projets réalisés : <?php echo $projectsCount; ?>"><?php echo $projectsCount; ?></div>
            <div class="stat-label">Projets<br>réalisés</div>
        </div>
    </div>

    <?php endif; ?>
</div>

<!-- ── Modal : Demande d'absence (collaborateur) ── -->
<div id="absence-request-modal" class="form-overlay" role="dialog" aria-modal="true"
     aria-labelledby="absence-request-title" style="display:none;">
    <div class="edit-form" style="max-width:440px;">
        <div class="form-header">
            <h3 id="absence-request-title"><i class="fas fa-calendar-times me-2" aria-hidden="true"></i>Demander une absence</h3>
            <button type="button" id="absence-request-close" class="btn-close" aria-label="Fermer">×</button>
        </div>
        <div class="form-content">
            <div class="form-group">
                <label for="ar-reason">Motif</label>
                <input type="text" id="ar-reason" placeholder="Congé, maladie, formation...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ar-start">Date de début *</label>
                    <input type="date" id="ar-start" required
                           aria-required="true" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="ar-end">Date de fin *</label>
                    <input type="date" id="ar-end" required
                           aria-required="true" min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div id="ar-error"   class="alert alert-danger"  role="alert"   style="display:none;"></div>
            <div id="ar-success" class="alert alert-success" role="status"  style="display:none;"></div>
        </div>
        <div class="form-actions">
            <button type="button" id="absence-request-cancel" class="btn-cancel">Annuler</button>
            <button type="button" id="absence-request-submit" class="btn-save">Envoyer la demande</button>
        </div>
    </div>
</div>

<?php
// Fichier : views/project/details.php
$project    = $project ?? [];
$tasks      = $tasks ?? [];
$stats      = $stats ?? [];
$team       = $team ?? [];
$states     = $states ?? [];
$taskDevelopers = $taskDevelopers ?? [];
$userRole   = $userRole ?? 'USER';
$canCreate  = $canCreate ?? false;
$canDelete  = $canDelete ?? false;
$allUsers   = $allUsers ?? [];
?>

<div class="project-details">
    <div class="main-content">
        <div class="project-details-card">

                <!-- Back Link & Header -->
                <div class="mb-4">
                    <a href="/projects" class="pd-back-link mb-3 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="pd-title mb-2"><?php echo htmlspecialchars($project['name']); ?></h1>
                            <p class="pd-description" style="max-width: 800px;"><?php echo htmlspecialchars($project['description'] ?? 'Aucune description'); ?></p>
                        </div>
                        <?php if ($canCreate || $canDelete) : ?>
                        <div class="position-relative" style="display:inline-block;">
                            <button id="pd-options-btn" class="pd-options-btn" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="pd-options-menu" style="display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.1);min-width:160px;z-index:100;">
                                <?php if ($canCreate) : ?>
                                <button class="pd-menu-item" id="pd-menu-edit" style="display:block;width:100%;text-align:left;padding:.6rem 1rem;background:none;border:none;cursor:pointer;font-size:.9rem;white-space:nowrap;">
                                    <i class="fas fa-pencil-alt me-2 text-muted"></i>Modifier le projet
                                </button>
                                <?php endif; ?>
                                <?php if ($canDelete) : ?>
                                <button class="pd-menu-item" id="pd-menu-delete" style="display:block;width:100%;text-align:left;padding:.6rem 1rem;background:none;border:none;cursor:pointer;font-size:.9rem;color:#dc3545;white-space:nowrap;">
                                    <i class="fas fa-trash-alt me-2"></i>Supprimer le projet
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Cards Row -->
                <div class="row g-4 mb-5">
                    <!-- Progression -->
                    <div class="col-md-6 col-lg-2">
                        <div class="pd-stat-card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="pd-stat-label">Progression</span>
                                <i class="far fa-check-circle pd-icon pd-icon--primary"></i>
                            </div>
                            <h2 class="pd-stat-value mb-2"><?php echo $stats['progress']; ?>%</h2>
                            <div class="pd-progress-track">
                                <div class="pd-progress-bar" role="progressbar" style="width: <?php echo $stats['progress']; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tâches complétées -->
                    <div class="col-md-6 col-lg-2">
                        <div class="pd-stat-card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="pd-stat-label">Tâches complétées</span>
                                <i class="far fa-check-circle pd-icon pd-icon--success"></i>
                            </div>
                            <h2 class="pd-stat-value mb-2"><?php echo $stats['completed']; ?>/<?php echo $stats['total']; ?></h2>
                        </div>
                    </div>

                    <!-- En cours -->
                    <div class="col-md-6 col-lg-2">
                        <div class="pd-stat-card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="pd-stat-label">En cours</span>
                                <i class="far fa-clock pd-icon pd-icon--primary"></i>
                            </div>
                            <h2 class="pd-stat-value mb-2"><?php echo $stats['in_progress']; ?></h2>
                        </div>
                    </div>

                    <!-- Échéance -->
                    <div class="col-md-6 col-lg-3">
                        <div class="pd-stat-card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="pd-stat-label">Échéance</span>
                                <i class="far fa-calendar pd-icon pd-icon--accent"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="pd-stat-deadline"><?php echo !empty($project['theoricalDeadLine']) ? (new DateTime($project['theoricalDeadLine']))->format('d M Y') : 'Non définie'; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Effort widget -->
                    <div class="col-md-6 col-lg-3">
                        <div class="pd-stat-card h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="pd-stat-label">Effort (h)</span>
                                <i class="fas fa-stopwatch pd-icon pd-icon--accent"></i>
                            </div>
                            <h2 class="pd-stat-value mb-1"><?php echo $stats['effort_made'] ?? 0; ?> / <?php echo $stats['effort_required'] ?? 0; ?></h2>
                            <?php
                                $effortPct = ($stats['effort_required'] ?? 0) > 0
                                    ? min(100, round(($stats['effort_made'] ?? 0) / $stats['effort_required'] * 100))
                                    : 0;
                            ?>
                            <div class="pd-progress-track">
                                <div class="pd-progress-bar" role="progressbar"
                                     aria-valuenow="<?php echo $effortPct; ?>"
                                     style="width: <?php echo $effortPct; ?>%; background: var(--accent, #f59e0b);"></div>
                            </div>
                            <span class="text-muted small"><?php echo $effortPct; ?>% accompli</span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Tasks Section -->
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="pd-section-title">Tâches</h3>
                            <?php if ($canCreate) : ?>
                            <button id="btn-new-task" class="btn-primary btn-sm">
                                <i class="fas fa-plus me-2"></i>Nouvelle tâche
                            </button>
                            <?php endif; ?>
                        </div>

                        <!-- Filters -->
                        <div class="d-flex gap-2 mb-4" id="pd-filter-bar">
                            <button class="pd-filter-btn active" data-filter="all">Toutes (<?php echo $stats['total']; ?>)</button>
                            <button class="pd-filter-btn" data-filter="todo">À faire (<?php echo $stats['todo']; ?>)</button>
                            <button class="pd-filter-btn" data-filter="in_progress">En cours (<?php echo $stats['in_progress']; ?>)</button>
                            <button class="pd-filter-btn" data-filter="done">Terminées (<?php echo $stats['completed']; ?>)</button>
                        </div>

                        <!-- Task List -->
                        <div class="d-flex flex-column gap-3">
                            <?php if (empty($tasks)) { ?>
                                <div class="pd-empty-tasks">
                                    Aucune tâche pour ce projet.
                                </div>
                            <?php } else { ?>
                                <?php foreach ($tasks as $task) { ?>
                                    <?php
                                        $stateName   = isset($states[$task->getStateId()]) ? $states[$task->getStateId()]->getName() : 'Inconnu';
                                        $isCompleted = false !== stripos($stateName, 'term') || false !== stripos($stateName, 'done') || false !== stripos($stateName, 'clos') || false !== stripos($stateName, 'fini');
                                        $isTodo      = false !== stripos($stateName, 'todo') || false !== stripos($stateName, 'faire') || false !== stripos($stateName, 'attente') || false !== stripos($stateName, 'backlog');
                                        $stateCat    = $isCompleted ? 'done' : ($isTodo ? 'todo' : 'in_progress');
                                    ?>
                                    <div class="pd-task-card p-3" data-task-id="<?php echo htmlspecialchars($task->getId()); ?>" data-state-cat="<?php echo $stateCat; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex gap-3">
                                                <div class="mt-1">
                                                    <?php
                                                        // stateName already computed above
                                                        $isCompleted = false !== stripos($stateName, 'term') || false !== stripos($stateName, 'done');
                                    ?>
                                                    <i class="far <?php echo $isCompleted ? 'fa-check-circle text-success' : 'fa-clock text-primary'; ?> fa-lg"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <h5 class="mb-0"><?php echo htmlspecialchars($task->getName()); ?></h5>
                                                    </div>
                                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($task->getDescription() ?? '', 0, 100)); ?>...</p>
                                                    
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($stateName); ?></span>
                                                        
                                                        <?php if ($task->getPriority()) { ?>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Haute</span>
                                                        <?php } ?>
                                                        
                                                        <?php
                                            $devId = $taskDevelopers[$task->getId()] ?? null;
                                    if ($devId && isset($team[$devId])) {
                                        ?>
                                                            <div class="d-flex align-items-center gap-1 text-muted small">
                                                                <i class="far fa-user"></i>
                                                                <?php echo htmlspecialchars($team[$devId]['user']->getFirstname()); ?>
                                                                <?php if (!empty($team[$devId]['is_absent'])) : ?>
                                                                    <span class="badge bg-warning text-dark ms-1" title="Développeur absent aujourd'hui"><i class="fas fa-user-clock" aria-hidden="true"></i></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php } ?>
                                                        
                                                        <div class="d-flex align-items-center gap-1 text-muted small">
                                                            <i class="far fa-calendar-alt"></i>
                                                            <?php echo $task->getTheoreticalEndDate() ? $task->getTheoreticalEndDate()->format('d/m/Y') : 'N/A'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($canCreate) : ?>
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:160px;padding:.5rem;">
                                                    <li><a class="dropdown-item py-2 pd-edit-task-btn" href="#" data-task-id="<?php echo htmlspecialchars($task->getId()); ?>"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                                    <li><a class="dropdown-item py-2 pd-close-task-btn" href="#" data-task-id="<?php echo htmlspecialchars($task->getId()); ?>"><i class="fas fa-check-circle me-2"></i>Clôturer la tâche</a></li>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Team Section -->
                    <div class="col-lg-4">
                        <div class="pd-team-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="pd-section-title mb-0">
                                    <i class="fas fa-users me-2"></i>Équipe du projet
                                </h5>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <?php if (empty($team)) { ?>
                                    <div class="text-muted small fst-italic">Aucun membre assigné.</div>
                                <?php } else { ?>
                                    <?php foreach ($team as $memberData) { ?>
                                        <?php
                                            $user = $memberData['user'];
                                            $isAbsent = !empty($memberData['is_absent']);
                                            $absStart = !empty($memberData['absence']['startdate']) ? (new DateTime($memberData['absence']['startdate']))->format('d/m') : '';
                                            $absEnd   = !empty($memberData['absence']['enddate'])   ? (new DateTime($memberData['absence']['enddate']))->format('d/m') : '';
                                        ?>
                                        <div class="<?php echo $isAbsent ? 'p-2 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25' : ''; ?>" style="width:100%;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="pd-avatar flex-shrink-0" style="<?php echo $isAbsent ? 'opacity:.6;' : ''; ?>">
                                                    <?php echo strtoupper(substr($user->getFirstname(), 0, 1) . substr($user->getLastname(), 0, 1)); ?>
                                                </div>
                                                <div style="min-width:0;">
                                                    <div class="fw-medium"><?php echo htmlspecialchars($user->getFirstname() . ' ' . $user->getLastname()); ?></div>
                                                    <div class="pd-avatar-meta"><?php echo $memberData['role']; ?> · <?php echo $memberData['active_tasks']; ?> tâche(s) active(s)</div>
                                                    <?php if ($isAbsent) : ?>
                                                        <span class="badge bg-warning text-dark mt-1" style="font-size:.7rem;">
                                                            <i class="fas fa-user-clock me-1" aria-hidden="true"></i>Absent du <?php echo $absStart; ?> au <?php echo $absEnd; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>

                            <?php if ($canCreate) : ?>
                            <!-- <button id="btn-add-member" class="btn-primary w-100 mt-4">
                                <i class="fas fa-plus me-2"></i>Ajouter un membre
                            </button> -->
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

        </div><!-- /.project-details-card -->
    </div>
</div>

<!-- ── Hidden project data for JS ── -->
<script id="pd-project-data" type="application/json">
{
    "projectId": "<?php echo htmlspecialchars($project['id'] ?? ''); ?>",
    "projectName": "<?php echo htmlspecialchars($project['name'] ?? ''); ?>",
    "canCreate": <?php echo $canCreate ? 'true' : 'false'; ?>,
    "canDelete": <?php echo $canDelete ? 'true' : 'false'; ?>,
    "states": <?php echo json_encode(array_map(fn($s) => ['id' => $s->getId(), 'name' => $s->getName()], array_values($states))); ?>,
    "tasks": <?php echo json_encode(array_map(fn($t) => ['id' => $t->getId(), 'name' => $t->getName()], $tasks)); ?>,
    "users": <?php echo json_encode(array_map(fn($u) => ['id' => $u->getId(), 'name' => $u->getFirstname() . ' ' . $u->getLastname()], $allUsers)); ?>
}
</script>

<!-- ── Modal : Nouvelle tâche ── -->
<div id="pd-modal-new-task" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pd-modal-new-task-title" style="display:none;">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <h3 id="pd-modal-new-task-title" style="font-size:1rem;font-weight:600;margin:0;">Nouvelle tâche</h3>
            <button type="button" class="btn-close pd-modal-close" data-target="pd-modal-new-task" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
            <div class="users-form-group">
                <label for="nt-name">Nom *</label>
                <input type="text" id="nt-name" class="form-control" required>
            </div>
            <div class="users-form-group" style="margin-top:.75rem;">
                <label for="nt-description">Description</label>
                <textarea id="nt-description" class="form-control" rows="3"></textarea>
            </div>
            <div class="row g-2" style="margin-top:.75rem;">
                <div class="col-6">
                    <label for="nt-effort">Effort requis (h) *</label>
                    <input type="number" id="nt-effort" class="form-control" min="0.01" step="0.5" placeholder="ex: 4">
                </div>
                <div class="col-6">
                    <label for="nt-priority">Priorité</label>
                    <select id="nt-priority" class="form-control">
                        <option value="">Normale</option>
                        <option value="high">Haute</option>
                        <option value="low">Basse</option>
                    </select>
                </div>
            </div>
            <div class="users-form-group" style="margin-top:.75rem;">
                <label for="nt-state">Statut</label>
                <select id="nt-state" class="form-control">
                    <option value="">— Aucun —</option>
                    <?php foreach ($states as $state) : ?>
                    <option value="<?php echo htmlspecialchars($state->getId()); ?>"><?php echo htmlspecialchars($state->getName()); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="users-form-group" style="margin-top:.75rem;">
                <label for="nt-developer">Assigné à</label>
                <select id="nt-developer" class="form-control">
                    <option value="">— Non assigné —</option>
                    <?php foreach ($allUsers as $u) : ?>
                    <option value="<?php echo htmlspecialchars($u->getId()); ?>"><?php echo htmlspecialchars($u->getFirstname() . ' ' . $u->getLastname()); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-2" style="margin-top:.75rem;">
                <div class="col-6">
                    <label for="nt-begin">Date début</label>
                    <input type="date" id="nt-begin" class="form-control">
                </div>
                <div class="col-6">
                    <label for="nt-end">Échéance *</label>
                    <input type="date" id="nt-end" class="form-control" required>
                </div>
            </div>
            <div id="nt-error" style="color:var(--danger,#dc3545);font-size:.85rem;margin-top:.5rem;display:none;"></div>
        </div>
        <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end;padding:.75rem 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm pd-modal-close" data-target="pd-modal-new-task">Annuler</button>
            <button type="button" id="nt-submit" class="btn btn-primary btn-sm">Créer</button>
        </div>
    </div>
</div>

<!-- ── Modal : Ajouter un membre (assigner user à tâche) ── -->
<div id="pd-modal-add-member" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pd-modal-add-member-title" style="display:none;">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3 id="pd-modal-add-member-title" style="font-size:1rem;font-weight:600;margin:0;">Assigner un membre à une tâche</h3>
            <button type="button" class="btn-close pd-modal-close" data-target="pd-modal-add-member" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
            <div class="users-form-group">
                <label for="am-task">Tâche</label>
                <select id="am-task" class="form-control">
                    <option value="">— Sélectionner une tâche —</option>
                    <?php foreach ($tasks as $t) : ?>
                    <option value="<?php echo htmlspecialchars($t->getId()); ?>"><?php echo htmlspecialchars($t->getName()); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="users-form-group" style="margin-top:.75rem;">
                <label for="am-user">Collaborateur</label>
                <select id="am-user" class="form-control">
                    <option value="">— Sélectionner un utilisateur —</option>
                    <?php foreach ($allUsers as $u) : ?>
                    <option value="<?php echo htmlspecialchars($u->getId()); ?>"><?php echo htmlspecialchars($u->getFirstname() . ' ' . $u->getLastname()); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="am-error" style="color:var(--danger,#dc3545);font-size:.85rem;margin-top:.5rem;display:none;"></div>
            <div id="am-success" style="color:var(--success,#28a745);font-size:.85rem;margin-top:.5rem;display:none;"></div>
        </div>
        <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end;padding:.75rem 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm pd-modal-close" data-target="pd-modal-add-member">Annuler</button>
            <button type="button" id="am-submit" class="btn btn-primary btn-sm">Assigner</button>
        </div>
    </div>
</div>

<!-- ── Modal : Confirmer suppression projet ── -->
<div id="pd-modal-delete-project" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pd-modal-delete-title" style="display:none;">
    <div class="modal-box" style="max-width:400px;">
        <div class="modal-header">
            <h3 id="pd-modal-delete-title" style="font-size:1rem;font-weight:600;margin:0;">Supprimer le projet</h3>
            <button type="button" class="btn-close pd-modal-close" data-target="pd-modal-delete-project" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" style="padding:1.25rem;">
            <p>Êtes-vous sûr de vouloir supprimer le projet <strong><?php echo htmlspecialchars($project['name'] ?? ''); ?></strong> ? Cette action est irréversible.</p>
            <div id="dp-error" style="color:var(--danger,#dc3545);font-size:.85rem;margin-top:.5rem;display:none;"></div>
        </div>
        <div class="modal-footer" style="display:flex;gap:.5rem;justify-content:flex-end;padding:.75rem 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm pd-modal-close" data-target="pd-modal-delete-project">Annuler</button>
            <button type="button" id="dp-submit" class="btn btn-danger btn-sm">Supprimer</button>
        </div>
    </div>
</div>

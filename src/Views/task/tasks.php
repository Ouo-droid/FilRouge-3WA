<?php
// Fichier : views/task/tasks.php
// Récupération des tasks depuis le contrôleur
$tasks    = $tasks ?? [];
$states   = $states ?? [];
$projects = $projects ?? [];
$users    = $users ?? [];
$userRole = $userRole ?? 'USER';
$canCreate = in_array($userRole, ['ADMIN', 'CDP', 'PDG'], true);
$canEdit   = in_array($userRole, ['ADMIN', 'CDP', 'PDG'], true);
$canDelete = in_array($userRole, ['ADMIN', 'PDG'], true);

// Calcul des statistiques
$totalTasks = count($tasks);
$highPriorityTasks = 0;
$upcomingDeadlines = 0; // Tâches dont l'échéance est dans les 7 prochains jours

$stateCounts = [];
if (!empty($states)) {
    foreach ($states as $state) {
        $stateCounts[$state->getId()] = 0;
    }
}

$now = new DateTime();
$nextWeek = (new DateTime())->modify('+7 days');

foreach ($tasks as $task) {
    if ('high' === strtolower($task['priority'] ?? '')) {
        ++$highPriorityTasks;
    }

    if (!empty($task['theoricalEndDate'])) {
        $endDate = new DateTime($task['theoricalEndDate']);
        if ($endDate > $now && $endDate <= $nextWeek) {
            ++$upcomingDeadlines;
        }
    }

    if (!empty($task['state_id'])) {
        if (isset($stateCounts[$task['state_id']])) {
            ++$stateCounts[$task['state_id']];
        } else {
            $stateCounts[$task['state_id']] = 1;
        }
    }
}

function getPriorityClass($priority)
{
    switch (strtolower($priority)) {
        case 'high': return 'badge-high';
        case 'medium': return 'badge-medium';
        case 'low': return 'badge-low';
        default: return 'badge-medium';
    }
}

function getPriorityLabel($priority)
{
    switch (strtolower($priority)) {
        case 'high': return 'Haute';
        case 'medium': return 'Moyenne';
        case 'low': return 'Basse';
        default: return 'Moyenne';
    }
}

function calculateDuration($start, $end)
{
    if (!$start || !$end) {
        return null;
    }
    $s = new DateTime($start);
    $e = new DateTime($end);
    $diff = $s->diff($e);

    return $diff->days * 24 + $diff->h + ($diff->i / 60);
}
?>

<div class="tasks-page">
                <!-- Header -->
                <div class="page-header">
                    <h1><?php echo $userRole === 'USER' ? 'Mes Tâches' : 'Gestion des Tâches'; ?></h1>
                    <p><?php echo $userRole === 'USER' ? 'Vos tâches assignées et leur avancement' : 'Suivez et gérez l\'avancement de vos projets'; ?></p>
                </div>

                <div id="loading" class="loading" style="display: none;">
                    <p>Chargement...</p>
                </div>
                <div id="error" style="display: none;" class="alert alert-warning"></div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Total Tâches</div>
                            <div class="stat-value"><?php echo $totalTasks; ?></div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Priorité Haute</div>
                            <div class="stat-value"><?php echo $highPriorityTasks; ?></div>
                        </div>
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Échéances Proches</div>
                            <div class="stat-value"><?php echo $upcomingDeadlines; ?></div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                     <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Tâches Actives</div>
                            <div class="stat-value"><?php echo $totalTasks; ?></div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-running"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="task-filters mb-4" role="group" aria-label="Filtrer les tâches par statut">
                    <button class="filter-btn active" data-filter="all" aria-pressed="true">
                        Toutes (<?php echo $totalTasks; ?>)
                    </button>
                    <?php if (!empty($states)) { ?>
                        <?php foreach ($states as $state) { ?>
                            <button class="filter-btn" data-filter="<?php echo $state->getId(); ?>" aria-pressed="false">
                                <?php echo htmlspecialchars($state->getName()); ?>
                                (<?php echo $stateCounts[$state->getId()] ?? 0; ?>)
                            </button>
                        <?php } ?>
                    <?php } ?>
                </div>

                <!-- Advanced Filters -->
                <?php if ($userRole !== 'USER' && (!empty($projects) || !empty($users))) : ?>
                <div class="task-advanced-filters mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <?php if (!empty($projects)) : ?>
                    <div>
                        <label for="filter-project" class="visually-hidden">Filtrer par projet</label>
                        <select id="filter-project" class="form-select form-select-sm" style="min-width:180px;">
                            <option value="">Tous les projets</option>
                            <?php foreach ($projects as $proj) : ?>
                                <option value="<?php echo htmlspecialchars($proj->getId()); ?>">
                                    <?php echo htmlspecialchars($proj->getName()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($users)) : ?>
                    <div>
                        <label for="filter-user" class="visually-hidden">Filtrer par utilisateur</label>
                        <select id="filter-user" class="form-select form-select-sm" style="min-width:180px;">
                            <option value="">Tous les utilisateurs</option>
                            <?php foreach ($users as $u) : ?>
                                <option value="<?php echo htmlspecialchars($u->getId()); ?>">
                                    <?php echo htmlspecialchars($u->getFirstname() . ' ' . $u->getLastname()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Actions Bar -->
                <div class="actions-bar">
                    <div class="search-container">
                        <label for="task-search" class="visually-hidden">Rechercher une tâche</label>
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="search" id="task-search" placeholder="Rechercher une tâche, un projet..." aria-label="Rechercher une tâche ou un projet">
                    </div>
                    <?php if ($canCreate) : ?>
                    <button id="create-task-btn" class="btn-new-task">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Nouvelle Tâche
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['message'])) { ?>
                    <div class="alert alert-info mb-4">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php } ?>

                <?php if (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-warning mb-4">
                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php } ?>

                <!-- Tasks List -->
                <div class="tasks-list" id="task-list">
                    <?php if (empty($tasks)) { ?>
                        <div class="no-tasks text-center p-5">
                            <p class="text-muted"><?php echo $canCreate ? 'Aucune tâche trouvée. Créez votre première tâche !' : 'Aucune tâche ne vous est assignée pour le moment.'; ?></p>
                        </div>
                    <?php } else { ?>
                        <?php foreach ($tasks as $task) {
                            $priority = strtolower($task['priority'] ?? 'medium');
                            $priorityClass = getPriorityClass($priority);
                            $priorityText = getPriorityLabel($priority);
                            $status = $task['state_name'] ?? 'À définir';

                            $estimatedDuration = calculateDuration($task['beginDate'] ?? null, $task['theoricalEndDate'] ?? null);
                            $realDuration = calculateDuration($task['beginDate'] ?? null, $task['realEndDate'] ?? null);

                            $variance = ($estimatedDuration && $realDuration) ? ($realDuration - $estimatedDuration) : null;
                            $varianceClass = ($variance && $variance > 0) ? 'text-danger' : 'text-success';
                            $varianceSign = ($variance && $variance > 0) ? '+' : '';
                            ?>
                            <div class="task-card <?php echo !empty($task['dev_absent']) ? 'task-card--absent' : ''; ?>"
                                 data-project-id="<?php echo htmlspecialchars($task['project_id'] ?? ''); ?>"
                                 data-developer-id="<?php echo htmlspecialchars($task['developer_id'] ?? ''); ?>">

                                <!-- Icône statut -->
                                <div class="task-card__icon">
                                    <i class="far fa-clock text-primary"></i>
                                </div>

                                <!-- Nom + description -->
                                <div class="task-card__main">
                                    <div class="task-card__name"><?php echo htmlspecialchars($task['name']); ?></div>
                                    <div class="task-card__desc"><?php echo htmlspecialchars($task['description'] ?? ''); ?></div>
                                </div>

                                <!-- Badges -->
                                <div class="task-card__badges">
                                    <span class="badge badge-tasks"><?php echo htmlspecialchars($status); ?></span>
                                    <span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars($priorityText); ?></span>
                                    <?php if (!empty($task['project_name'])) { ?>
                                        <span class="task-card__meta"><i class="fas fa-folder text-muted me-1"></i><?php echo htmlspecialchars($task['project_name']); ?></span>
                                    <?php } ?>
                                    <?php if (!empty($task['developer_firstname'])) { ?>
                                        <span class="task-card__meta"><i class="far fa-user text-muted me-1"></i><?php echo htmlspecialchars($task['developer_firstname'] . ' ' . $task['developer_lastname']); ?></span>
                                    <?php } ?>
                                    <?php if (!empty($task['dev_absent'])) :
                                        $absStart = !empty($task['dev_absence_start']) ? (new DateTime($task['dev_absence_start']))->format('d/m') : '';
                                        $absEnd   = !empty($task['dev_absence_end'])   ? (new DateTime($task['dev_absence_end']))->format('d/m') : '';
                                    ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-user-clock me-1" aria-hidden="true"></i>Dev absent du <?php echo $absStart; ?> au <?php echo $absEnd; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Effort -->
                                <div class="task-card__effort">
                                    <span class="time-item"><i class="fas fa-stopwatch text-primary"></i> Estimé&nbsp;: <strong><?php echo !empty($task['effortrequired']) ? $task['effortrequired'] . 'h' : '-'; ?></strong></span>
                                    <span class="time-item"><i class="fas fa-history text-success"></i> Réel&nbsp;: <strong><?php echo !empty($task['effortmade']) ? $task['effortmade'] . 'h' : '-'; ?></strong></span>
                                    <?php
                                        $effort = $task['effortrequired'] ?? null;
                                        $made   = $task['effortmade'] ?? null;
                                        if ($effort && $made) {
                                            $diff = $made - $effort;
                                            echo '<span class="variance-badge ' . ($diff > 0 ? 'text-danger' : 'text-success') . '">' . ($diff > 0 ? '+' : '') . round($diff, 1) . 'h</span>';
                                        }
                                    ?>
                                    <?php if (!empty($task['beginDate'])) { ?>
                                        <span class="task-card__meta"><i class="far fa-calendar-alt text-muted me-1"></i><?php echo (new DateTime($task['beginDate']))->format('d/m/Y'); ?></span>
                                    <?php } ?>
                                </div>

                                <!-- Actions -->
                                <div class="task-card__actions">
                                    <?php if ('terminées' !== strtolower(trim($task['state_name'] ?? ''))) { ?>
                                    <button class="btn btn-outline-secondary btn-sm manage-time-btn" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-clock me-1"></i> Gérer le temps
                                    </button>
                                    <?php } else { ?>
                                    <span class="badge badge-tasks"><i class="fas fa-check-circle me-1"></i> Terminée</span>
                                    <?php } ?>

                                    <div class="dropdown">
                                        <button class="btn-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:160px;padding:.5rem;">
                                            <li><a class="dropdown-item py-2 view-task-btn" href="#" data-task-id="<?php echo $task['id']; ?>"><i class="fas fa-eye me-2"></i>Voir détails</a></li>
                                            <?php if ($canEdit) : ?>
                                            <li><a class="dropdown-item py-2 edit-task-btn" href="#" data-task-id="<?php echo $task['id']; ?>"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                            <?php endif; ?>
                                            <?php if ($canDelete) : ?>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li><a class="dropdown-item text-danger py-2 delete-btn" href="#" data-task-id="<?php echo $task['id']; ?>" data-task-name="<?php echo htmlspecialchars($task['name']); ?>"><i class="fas fa-trash me-2"></i>Supprimer</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
</div>

<!-- Modal pour afficher les détails d'une tâche -->
<div id="taskModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="taskModalTitle">Détails de la Tâche</h3>
            <button class="close" id="close-task-modal" aria-label="Fermer">&times;</button>
        </div>
        <div class="modal-body">
            <div id="task-modal-message" style="display: none;">
                <!-- Message d'erreur ou de confirmation -->
            </div>

            <div id="task-modal-details" style="display: none;">
                <div class="detail-group">
                    <div class="detail-label">Nom de la tâche</div>
                    <div class="detail-value" id="task-modal-name"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Description</div>
                    <div class="detail-value" id="task-modal-description"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Type</div>
                    <div class="detail-value" id="task-modal-type"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Format</div>
                    <div class="detail-value" id="task-modal-format"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Priorité</div>
                    <div class="detail-value" id="task-modal-priority"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Échéance théorique</div>
                    <div class="detail-value" id="task-modal-theoretical-end-date"></div>
                </div>

                <div class="detail-group">
                    <div class="detail-label">Échéance réelle</div>
                    <div class="detail-value" id="task-modal-real-end-date"></div>
                </div>
            </div>
        </div>
    </div>
</div>

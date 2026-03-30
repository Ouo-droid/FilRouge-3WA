<?php
// Fichier : views/project/project.php
$projects          = $projects ?? [];
$userRole          = $userRole ?? 'USER';
$statusStats       = $statusStats ?? ['en_attente' => 0, 'en_cours' => 0, 'termine' => 0, 'retarde' => 0, 'annule' => 0];
$states            = $states ?? [];
$stateCounts       = $stateCounts ?? [];
$upcomingDeadlines = $upcomingDeadlines ?? 0;
$canCreate         = in_array($userRole, ['ADMIN', 'CDP', 'PDG'], true);
$canDelete         = in_array($userRole, ['ADMIN', 'PDG'], true);
$totalProjects     = count($projects);
?>

<div class="projects-page">

    <!-- Header -->
    <div class="page-header">
        <h1><?php echo $canCreate ? 'Gestion des Projets' : 'Mes Projets'; ?></h1>
        <p><?php echo $canCreate ? 'Gérez vos projets et suivez leur avancement' : 'Consultez les projets sur lesquels vous êtes assigné'; ?></p>
    </div>

    <div id="loading" class="loading" style="display: none;"><p>Chargement...</p></div>
    <div id="error" style="display: none;" class="alert alert-warning"></div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Projets</div>
                <div class="stat-value"><?php echo $totalProjects; ?></div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-folder"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">En cours</div>
                <div class="stat-value"><?php echo $statusStats['en_cours']; ?></div>
            </div>
            <div class="stat-icon green"><i class="fas fa-spinner"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Échéances Proches</div>
                <div class="stat-value"><?php echo $upcomingDeadlines; ?></div>
            </div>
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Terminés</div>
                <div class="stat-value"><?php echo $statusStats['termine']; ?></div>
            </div>
            <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="task-filters mb-4" role="group" aria-label="Filtrer les projets par statut">
        <button class="filter-btn active" data-filter="all" aria-pressed="true">
            Tous (<?php echo $totalProjects; ?>)
        </button>
        <?php foreach ($states as $stateId => $state) { ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars((string) $stateId); ?>" aria-pressed="false">
                <?php echo htmlspecialchars($state->getName()); ?>
                (<?php echo $stateCounts[$stateId] ?? 0; ?>)
            </button>
        <?php } ?>
    </div>

    <!-- Actions Bar -->
    <div class="actions-bar">
        <div class="search-container">
            <label for="project-search" class="visually-hidden">Rechercher un projet</label>
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="project-search" placeholder="Rechercher un projet..." aria-label="Rechercher un projet">
        </div>
        <?php if ($canCreate) : ?>
        <button id="create-project-btn" class="btn-new-project">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Nouveau Projet
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

    <!-- Projects List -->
    <div class="projects-list" id="project-list">
        <?php if (empty($projects)) { ?>
            <div class="no-projects text-center p-5">
                <i class="fas fa-folder-open mb-3 fa-3x text-muted"></i>
                <p class="text-muted"><?php echo $canCreate ? 'Aucun projet trouvé. Créez votre premier projet !' : 'Aucun projet ne vous est assigné pour le moment.'; ?></p>
                <?php if ($canCreate) : ?>
                <button class="btn-new-project mt-3 create-project-trigger">
                    <i class="fas fa-plus me-1"></i> Nouveau Projet
                </button>
                <?php endif; ?>
            </div>
        <?php } else { ?>
            <?php foreach ($projects as $project) { ?>
                <div class="project-card"
                     data-state-id="<?php echo htmlspecialchars($project['state_id'] ?? ''); ?>"
                     data-project-name="<?php echo htmlspecialchars(strtolower($project['name'] ?? '')); ?>">

                    <!-- Icône -->
                    <div class="project-card__icon">
                        <i class="fas fa-folder text-primary"></i>
                    </div>

                    <!-- Nom + description -->
                    <div class="project-card__main">
                        <div class="project-card__name"><?php echo htmlspecialchars($project['name']); ?></div>
                        <?php if (!empty($project['description'])) { ?>
                            <div class="project-card__desc"><?php echo htmlspecialchars($project['description']); ?></div>
                        <?php } ?>
                    </div>

                    <!-- Badges statut + manager -->
                    <div class="project-card__badges">
                        <span class="badge badge-tasks"><?php echo htmlspecialchars($project['state_name'] ?? 'En attente'); ?></span>
                        <?php if (!empty($project['manager_firstname'])) { ?>
                            <span class="project-card__meta">
                                <i class="fas fa-user-tie text-muted me-1"></i>
                                <?php echo htmlspecialchars($project['manager_firstname'] . ' ' . $project['manager_lastname']); ?>
                            </span>
                        <?php } ?>
                    </div>

                    <!-- Barre de progression -->
                    <?php $prog = $project['progress'] ?? 0; ?>
                    <div class="project-card__progress-bar">
                        <div class="pc-prog-track">
                            <div class="pc-prog-fill <?php echo $prog >= 80 ? 'pc-prog-fill--success' : ($prog >= 40 ? 'pc-prog-fill--warning' : 'pc-prog-fill--danger'); ?>" style="width:<?php echo $prog; ?>%"></div>
                        </div>
                        <span class="pc-prog-label"><?php echo $prog; ?>%</span>
                    </div>

                    <!-- Tâches + participants -->
                    <div class="project-card__progress">
                        <span class="time-item">
                            <i class="fas fa-tasks text-primary"></i>
                            <strong><?php echo $project['task_count'] ?? 0; ?></strong>&nbsp;tâche<?php echo ($project['task_count'] ?? 0) > 1 ? 's' : ''; ?>
                        </span>
                        <span class="time-item">
                            <i class="fas fa-users text-success"></i>
                            <strong><?php echo $project['participants_count'] ?? 0; ?></strong>&nbsp;participant<?php echo ($project['participants_count'] ?? 0) > 1 ? 's' : ''; ?>
                        </span>
                    </div>

                    <!-- Échéance -->
                    <div class="project-card__deadline">
                        <?php if (!empty($project['theoricalDeadLine'])) { ?>
                            <span class="project-card__meta">
                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                <?php echo (new DateTime($project['theoricalDeadLine']))->format('d/m/Y'); ?>
                            </span>
                        <?php } else { ?>
                            <span class="project-card__meta text-muted">Aucune échéance</span>
                        <?php } ?>
                    </div>

                    <!-- Actions -->
                    <div class="project-card__actions">
                        <a href="/project/<?php echo $project['id']; ?>" class="pc-action-btn" title="Consulter">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($canCreate) : ?>
                        <a href="#" class="pc-action-btn pc-action-btn--edit edit-project-btn" title="Modifier" data-project-id="<?php echo $project['id']; ?>">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($canDelete) : ?>
                        <a href="#" class="pc-action-btn pc-action-btn--delete delete-project-btn" title="Supprimer"
                           data-project-id="<?php echo $project['id']; ?>"
                           data-project-name="<?php echo htmlspecialchars($project['name']); ?>">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<!-- Modals (conservés pour création/édition) -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Détails du Projet</h3>
            <button class="close" id="close-project-modal" aria-label="Fermer">&times;</button>
        </div>
        <div class="modal-body">
            <div id="project-modal-message" style="display: none;"></div>
            <div id="project-modal-details" style="display: none;">
                <div class="detail-group">
                    <div class="detail-label">Nom du projet</div>
                    <div class="detail-value" id="project-modal-name"></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Description</div>
                    <div class="detail-value" id="project-modal-description"></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Échéance théorique</div>
                    <div class="detail-value" id="project-modal-theoretical-deadline"></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Échéance réelle</div>
                    <div class="detail-value" id="project-modal-real-deadline"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="notificationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Notification</h3>
            <button class="close" id="close-modal" aria-label="Fermer">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modal-message" style="display: none;"></div>
            <div id="modal-user-details" style="display: none;">
                <div class="detail-group">
                    <div class="detail-label">Nom</div>
                    <div class="detail-value" id="modal-name"></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Email</div>
                    <div class="detail-value" id="modal-email"></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Rôle</div>
                    <div class="detail-value" id="modal-roles"></div>
                </div>
            </div>
        </div>
    </div>
</div>

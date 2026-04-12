<?php
$archivedProjects = $archivedProjects ?? [];
$archivedTasks    = $archivedTasks ?? [];
$userRole         = $userRole ?? 'USER';
?>

<div class="history-page">
    <div class="page-header">
        <h1>Historique des Archives</h1>
        <p>Retrouvez ici tous les projets et tâches archivés.</p>
    </div>

    <!-- Tabs for Projects and Tasks -->
    <div class="history-tabs mb-4">
        <button class="filter-btn active" data-tab="projects-tab">Projets Archivés (<?php echo count($archivedProjects); ?>)</button>
        <button class="filter-btn" data-tab="tasks-tab">Tâches Archivées (<?php echo count($archivedTasks); ?>)</button>
    </div>

    <!-- Archived Projects -->
    <div id="projects-tab" class="tab-content">
        <?php if (empty($archivedProjects)) { ?>
            <div class="no-data text-center p-5">
                <i class="fas fa-archive mb-3 fa-3x text-muted"></i>
                <p class="text-muted">Aucun projet archivé pour le moment.</p>
            </div>
        <?php } else { ?>
            <div class="projects-list">
                <?php foreach ($archivedProjects as $project) { ?>
                    <div class="project-card">
                        <div class="project-card__icon">
                            <i class="fas fa-folder text-secondary"></i>
                        </div>
                        <div class="project-card__main">
                            <div class="project-card__name"><?php echo htmlspecialchars($project['name']); ?></div>
                            <div class="project-card__desc text-muted"><?php echo htmlspecialchars($project['description'] ?? ''); ?></div>
                        </div>
                        <div class="project-card__badges">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($project['state_name'] ?? 'Archivé'); ?></span>
                            <?php if (!empty($project['manager_name'])) { ?>
                                <span class="project-card__meta">
                                    <i class="fas fa-user-tie text-muted me-1"></i>
                                    <?php echo htmlspecialchars($project['manager_name']); ?>
                                </span>
                            <?php } ?>
                        </div>
                        <div class="project-card__deadline">
                             <span class="project-card__meta">
                                <i class="fas fa-clock text-muted me-1"></i>
                                Archivé le : <?php echo (new DateTime($project['updatedat']))->format('d/m/Y H:i'); ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <!-- Archived Tasks -->
    <div id="tasks-tab" class="tab-content" style="display: none;">
        <?php if (empty($archivedTasks)) { ?>
            <div class="no-data text-center p-5">
                <i class="fas fa-tasks mb-3 fa-3x text-muted"></i>
                <p class="text-muted">Aucune tâche archivée pour le moment.</p>
            </div>
        <?php } else { ?>
            <div class="projects-list">
                <?php foreach ($archivedTasks as $task) { ?>
                    <div class="project-card">
                        <div class="project-card__icon">
                            <i class="fas fa-check-circle text-secondary"></i>
                        </div>
                        <div class="project-card__main">
                            <div class="project-card__name"><?php echo htmlspecialchars($task['name']); ?></div>
                            <div class="project-card__desc text-muted"><?php echo htmlspecialchars($task['description'] ?? ''); ?></div>
                        </div>
                        <div class="project-card__badges">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($task['state_name'] ?? 'Archivée'); ?></span>
                            <span class="project-card__meta">
                                <i class="fas fa-tag text-muted me-1"></i>
                                Type: <?php echo htmlspecialchars($task['type'] ?? ''); ?>
                            </span>
                        </div>
                        <div class="project-card__deadline">
                             <span class="project-card__meta">
                                <i class="fas fa-clock text-muted me-1"></i>
                                Archivée le : <?php echo (new DateTime($task['updatedat']))->format('d/m/Y H:i'); ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const tabId = this.getAttribute('data-tab');
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = (tab.id === tabId) ? 'block' : 'none';
        });
    });
});
</script>

<style>
.history-tabs {
    display: flex;
    gap: 10px;
}
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

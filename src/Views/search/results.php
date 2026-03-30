<?php
// Fichier : views/search/results.php
// Récupération des données depuis le contrôleur
$searchTerm = $searchTerm ?? '';
$results = $results ?? [];
$total = $total ?? 0;
$currentPage = $currentPage ?? 1;
$lastPage = $lastPage ?? 1;
$entityType = $entityType ?? 'all';
?>

<div class="main-content">
                <!-- Header -->
                <div class="page-header">
                    <h1><i class="fas fa-search me-2"></i>Recherche : <?php echo htmlspecialchars($searchTerm); ?></h1>
                    <div class="header-actions">
                        <span class="badge rounded-pill bg-light text-dark border">
                            <?php echo $total; ?> résultat(s)
                        </span>
                    </div>
                </div>

                <!-- Barre de recherche interne -->
                <div class="card mb-4 p-4 shadow-sm border-0 rounded-3">
                    <form method="GET" action="/search" id="searchForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="searchInput" class="form-label">Terme recherché</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input 
                                            type="text" 
                                            name="q" 
                                            id="searchInput"
                                            class="form-control border-start-0" 
                                            placeholder="Que recherchez-vous ?" 
                                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="entitySelect" class="form-label">Domaine</label>
                                    <select name="entity" id="entitySelect" class="form-select">
                                        <?php foreach ($searchableEntities as $key => $entity) { ?>
                                            <option value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $entityType === $key ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($entity['label']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Liste des résultats -->
                <?php if (!empty($results)) { ?>
                    <div class="card-grid">
                        <?php foreach ($results as $result) { ?>
                            <?php
                            $title = 'Sans titre';
                            $subtitle = '';
                            if (method_exists($result, 'getCompanyName')) {
                                $title = $result->getCompanyName();
                                $subtitle = 'Client';
                                $initials = substr($title, 0, 1);
                            } elseif (method_exists($result, 'getFirstname')) {
                                $title = $result->getFirstname() . ' ' . $result->getLastname();
                                $subtitle = 'Utilisateur';
                                $initials = substr($result->getFirstname(), 0, 1) . substr($result->getLastname(), 0, 1);
                            } elseif (method_exists($result, 'getName')) {
                                $title = $result->getName();
                                $subtitle = 'Projet / Tâche';
                                $initials = substr($title, 0, 1);
                            }
                            ?>
                            <div class="item-card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="user-avatar-circle me-3">
                                            <?php echo strtoupper($initials ?? '??'); ?>
                                        </div>
                                        <div>
                                            <span class="badge badge-<?php echo htmlspecialchars($entityType, ENT_QUOTES, 'UTF-8'); ?> mb-1">
                                                <?php echo htmlspecialchars($searchableEntities[$entityType]['label']); ?>
                                            </span>
                                            <h3 class="h-m mb-0"><?php echo htmlspecialchars($title); ?></h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-meta">
                                    <?php if (method_exists($result, 'getEmail')) { ?>
                                        <div class="meta-item">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <span class="meta-value"><?php echo htmlspecialchars($result->getEmail()); ?></span>
                                        </div>
                                    <?php } ?>

                                    <?php if (method_exists($result, 'getDescription') && $result->getDescription()) { ?>
                                        <div class="meta-item">
                                            <i class="fas fa-align-left text-muted me-2"></i>
                                            <span class="meta-value"><?php echo htmlspecialchars(substr($result->getDescription(), 0, 80)); ?>...</span>
                                        </div>
                                    <?php } ?>

                                    <div class="meta-item">
                                        <i class="fas fa-hashtag text-muted me-2"></i>
                                        <span class="meta-value">
                                            <?php
                                            if (method_exists($result, 'getId')) {
                                                echo 'ID: ' . htmlspecialchars($result->getId(), ENT_QUOTES, 'UTF-8');
                                            } elseif (method_exists($result, 'getNumSIRET')) {
                                                echo 'SIRET: ' . htmlspecialchars($result->getNumSIRET(), ENT_QUOTES, 'UTF-8');
                                            }
                            ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <a href="#" class="btn-sm btn-info text-dark text-decoration-none">
                                        <i class="fas fa-eye me-1"></i>Voir détails
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($lastPage > 1) { ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $lastPage; ++$i) { ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="?q=<?php echo urlencode($searchTerm); ?>&entity=<?php echo $entityType; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </nav>
                    <?php } ?>

                <?php } else { ?>
                    <div class="alert alert-info text-center p-5 rounded-3">
                        <i class="fas fa-search-minus mb-3 fa-3x text-muted"></i>
                        <p class="mb-0">Aucun résultat trouvé pour "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".</p>
                    </div>
                <?php } ?>
</div>

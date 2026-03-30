<?php
// Fichier : views/client/clients.php
// Récupération des données depuis le contrôleur
$clients   = $clients ?? [];
$totalClients = $stats['total'] ?? 0;
$userRole  = $userRole ?? 'USER';
$canCreate = in_array($userRole, ['ADMIN', 'CDP', 'PDG'], true);
$canDelete = in_array($userRole, ['ADMIN', 'PDG'], true);
?>

<div class="clients-page">
                <!-- Header -->
                <div class="page-header">
                    <h1>Gestion des Clients</h1>
                    <p>Gérez votre portefeuille clients et suivez vos opportunités</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Total Clients</div>
                            <div class="stat-value" aria-label="Total clients : <?php echo $totalClients; ?>"><?php echo $totalClients; ?></div>
                        </div>
                        <div class="stat-icon blue" aria-hidden="true">
                            <i class="fas fa-users" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="actions-bar">
                    <div class="search-container">
                        <label for="client-search" class="visually-hidden">Rechercher un client</label>
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="search" id="client-search" placeholder="Rechercher un client, une entreprise..." aria-label="Rechercher un client ou une entreprise">
                    </div>
                    <?php if ($canCreate) : ?>
                    <button id="create-client-btn" class="btn-new-client">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Nouveau Client
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Clients List -->
                <div class="clients-list" id="clients-list-container">
                    <?php if (empty($clients)) { ?>
                        <div class="no-clients text-center p-5">
                            <p class="text-muted">Aucun client trouvé. Créez votre premier client !</p>
                        </div>
                    <?php } else { ?>
                        <?php foreach ($clients as $client) {
                            $projectsCount = $client['projectCount'] ?? 0;
                            ?>
                            <div class="client-row" data-search-term="<?php echo strtolower(($client['companyName'] ?? '') . ' ' . ($client['contactFirstname'] ?? '') . ' ' . ($client['contactLastname'] ?? '')); ?>">
                                <div class="client-info">
                                    <div class="avatar" aria-hidden="true">
                                        <?php echo strtoupper(substr($client['companyName'] ?? '?', 0, 1)); ?>
                                    </div>
                                    <div class="details">
                                        <h3><?php echo htmlspecialchars($client['companyName'] ?? ''); ?></h3>
                                        <div class="company">
                                            <i class="fas fa-id-card" aria-hidden="true"></i>
                                            <span class="visually-hidden">SIRET : </span><?php echo htmlspecialchars($client['siret'] ?? ''); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="contact-info">
                                    <div><i class="fas fa-user" aria-hidden="true"></i> <span class="visually-hidden">Contact : </span><?php echo htmlspecialchars(trim(($client['contactFirstname'] ?? '') . ' ' . ($client['contactLastname'] ?? ''))); ?></div>
                                    <?php if (!empty($client['contactEmail'])) { ?>
                                        <div><i class="fas fa-envelope" aria-hidden="true"></i> <span class="visually-hidden">Email : </span><?php echo htmlspecialchars($client['contactEmail']); ?></div>
                                    <?php } ?>
                                </div>

                                <div class="location-info">
                                    <div><i class="fas fa-briefcase" aria-hidden="true"></i> <span class="visually-hidden">Secteur : </span><?php echo htmlspecialchars($client['workfield'] ?? 'Non spécifié'); ?></div>
                                </div>

                                <div class="metrics">
                                    <div class="label">Projets</div>
                                    <div class="value"><?php echo $projectsCount; ?></div>
                                </div>

                                <div class="actions-menu">
                                    <div class="dropdown">
                                        <button class="btn-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions pour <?php echo htmlspecialchars($client['companyName'] ?? ''); ?>" aria-haspopup="true">
                                            <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" role="menu" style="min-width: 160px; padding: 0.5rem;">
                                            <li role="none"><a class="dropdown-item py-2 view-client-btn" href="#" role="menuitem" data-siret="<?php echo $client['siret']; ?>"><i class="fas fa-eye me-2" aria-hidden="true"></i>Voir détails</a></li>
                                            <?php if ($canCreate) : ?>
                                            <li role="none"><a class="dropdown-item py-2 edit-client-btn" href="#" role="menuitem" data-siret="<?php echo $client['siret']; ?>"><i class="fas fa-edit me-2" aria-hidden="true"></i>Modifier</a></li>
                                            <?php endif; ?>
                                            <?php if ($canDelete) : ?>
                                            <li role="none"><hr class="dropdown-divider my-1"></li>
                                            <li role="none"><a class="dropdown-item text-danger py-2 delete-client-btn" href="#" role="menuitem" data-siret="<?php echo $client['siret']; ?>" data-name="<?php echo htmlspecialchars($client['companyName'] ?? ''); ?>"><i class="fas fa-trash me-2" aria-hidden="true"></i>Supprimer</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
</div>

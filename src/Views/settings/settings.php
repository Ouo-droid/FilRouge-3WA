<?php
/** @var Kentec\App\Model\User $user */
$user = $user ?? null;
?>

<div class="settings-page">
    <div class="main-content">
                <!-- Header -->
                <div class="settings-header">
                    <h1>Paramètres</h1>
                    <p>Gérez vos préférences et paramètres de compte</p>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav settings-tabs" role="tablist" aria-label="Navigation des paramètres">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-profil" href="#content-profil" role="tab" data-target="content-profil" aria-selected="true" aria-controls="content-profil"><i class="fas fa-user me-2" aria-hidden="true"></i>Profil</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-securite" href="#content-securite" role="tab" data-target="content-securite" aria-selected="false" aria-controls="content-securite"><i class="fas fa-shield-alt me-2" aria-hidden="true"></i>Sécurité</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-mentions" href="#content-mentions-legales" role="tab" data-target="content-mentions-legales" aria-selected="false" aria-controls="content-mentions-legales"><i class="fas fa-file-contract me-2" aria-hidden="true"></i>Mentions Légales</a>
                    </li>
                    <!-- <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#" role="tab" aria-selected="false" aria-disabled="true"><i class="fas fa-bell me-2" aria-hidden="true"></i>Notifications</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#" role="tab" aria-selected="false" aria-disabled="true"><i class="fas fa-paint-brush me-2" aria-hidden="true"></i>Apparence</a>
                    </li> -->
                </ul>

                <!-- Settings Card -->
                <div id="content-profil" class="settings-content" role="tabpanel" aria-labelledby="tab-profil">
                <div class="card settings-card overflow-hidden">
                    <div class="card-body">

                        <!-- Profile Avatar Section -->
                        <div class="settings-avatar-section">
                            <div class="settings-avatar" aria-hidden="true">
                                <?php
                                if ($user) {
                                    echo strtoupper(substr($user->getFirstname() ?? '', 0, 1) . substr($user->getLastname() ?? '', 0, 1));
                                } else {
                                    echo '??';
                                }
                                ?>
                            </div>
                            <!-- <div class="avatar-actions">
                                <button class="btn btn-outline-secondary btn-change" aria-label="Changer la photo de profil">Changer la photo</button>
                                <p>JPG, PNG ou GIF. Max 2 Mo.</p>
                            </div> -->
                            <h2 class="settings-avatar-title">Informations du profil</h2>
                        </div>

                        <!-- Profile Form -->
                        <form id="settings-form" data-user-id="<?php echo $user ? $user->getId() : ''; ?>">
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label for="firstname" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="firstname" value="<?php echo $user ? htmlspecialchars($user->getFirstname()) : ''; ?>" autocomplete="given-name">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="lastname" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="lastname" value="<?php echo $user ? htmlspecialchars($user->getLastname()) : ''; ?>" autocomplete="family-name">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo $user ? htmlspecialchars($user->getEmail()) : ''; ?>" autocomplete="email">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="role" class="form-label">Rôle</label>
                                    <input type="text" class="form-control" id="role" value="<?php echo $user && $user->getRoleName() ? htmlspecialchars('ADMIN' === $user->getRoleName() ? 'Administrateur' : 'Collaborateur') : ''; ?>" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="jobtitle" class="form-label">Titre de poste</label>
                                    <input type="text" class="form-control" id="jobtitle" value="<?php echo $user && $user->getJobtitle() ? htmlspecialchars($user->getJobtitle()) : ''; ?>" placeholder="Ex: Développeur Full-Stack" autocomplete="organization-title">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="fieldofwork" class="form-label">Domaine d'activité</label>
                                    <input type="text" class="form-control" id="fieldofwork" value="<?php echo $user && $user->getFieldofwork() ? htmlspecialchars($user->getFieldofwork()) : ''; ?>" placeholder="Ex: Développement Web">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Diplômes / Certifications</label>
                                    <div class="degree-add-row">
                                        <input type="text" class="form-control" id="degree-input" placeholder="Ex: Master Informatique, AWS Certified...">
                                        <button type="button" id="degree-add-btn" class="btn-degree-add" title="Ajouter">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <ul id="degree-list" class="degree-list">
                                        <?php if ($user && $user->getDegree()): foreach ($user->getDegree() as $d): ?>
                                        <li class="degree-item" data-value="<?php echo htmlspecialchars($d); ?>">
                                            <span class="degree-text"><?php echo htmlspecialchars($d); ?></span>
                                            <div class="degree-actions">
                                                <button type="button" class="degree-btn-edit" title="Modifier"><i class="fas fa-pen"></i></button>
                                                <button type="button" class="degree-btn-delete" title="Supprimer"><i class="fas fa-times"></i></button>
                                            </div>
                                        </li>
                                        <?php endforeach; endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-save d-flex align-items-center">
                                <i class="fas fa-save me-2" aria-hidden="true"></i> Enregistrer les modifications
                            </button>
                        </form>

                        <!-- <hr class="my-4">

                        <div class="delete-account-section mt-4">
                            <h3 class="text-danger">Zone de danger</h3>
                            <p class="text-muted mb-3">Une fois que vous supprimez votre compte, il n'y a pas de retour en arrière possible. Soyez-en sûr.</p>
                            <button type="button" id="btn-delete-account" class="btn btn-outline-danger d-flex align-items-center" aria-label="Supprimer mon compte définitivement — action irréversible">
                                <i class="fas fa-trash-alt me-2" aria-hidden="true"></i> Supprimer mon compte
                            </button>
                        </div> -->
                    </div>
                </div>
                </div>

                <!-- Mentions Legales Content -->
                <div id="content-mentions-legales" class="settings-content" style="display: none;">
                    <div class="card settings-card overflow-hidden">
                        <div class="card-body">
                            <h2>Mentions Légales</h2>

                            <div class="legal-section mb-4">
                                <h3>1. Éditeur du site</h3>
                                <p>AzTech SAS — Éditeur de la solution KenTec<br>
                                Immatriculée au RCS de Lille sous le numéro 987 654 321<br>
                                Siège social : 42 Rue de la Technologie, 59000 Lille<br>
                                Email : contact@kentec.com<br>
                                Téléphone : 03 20 00 00 00</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>2. Directeur de la publication</h3>
                                <p>La direction d'AzTech SAS, éditeur de la plateforme KenTec.</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>3. Hébergement</h3>
                                <p>Le site est hébergé par AWS Europe (Paris)<br>
                                31 Place des Corolles, 92400 Courbevoie<br>
                                Téléphone : 01 23 45 67 89</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>4. Propriété intellectuelle</h3>
                                <p>L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>5. Données personnelles</h3>
                                <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification, de portabilité et d'effacement de vos données personnelles.</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>6. Cookies</h3>
                                <p>Ce site utilise des cookies pour améliorer l'expérience utilisateur et réaliser des statistiques de visites. En poursuivant votre navigation, vous acceptez l'utilisation de cookies.</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>7. Responsabilité</h3>
                                <p>KenTec / AzTech SAS ne saurait être tenu responsable des erreurs ou omissions dans les informations diffusées ou des problèmes techniques rencontrés sur le site.</p>
                            </div>

                            <div class="legal-section mb-4">
                                <h3>8. Droit applicable</h3>
                                <p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.</p>
                            </div>

                            <div class="legal-footer mt-5 pt-3 border-top text-center text-muted">
                                <small>Dernière mise à jour : 28 mars 2026</small>
                                <br>
                                <small>© 2026 AzTech SAS — KenTec. Tous droits réservés.</small>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

                <!-- Sécurité Content -->
                <div id="content-securite" class="settings-content" style="display: none;">
                    <div class="card settings-card overflow-hidden">
                        <div class="card-body">
                            <h2>Changer le mot de passe</h2>
                            <p class="text-muted mb-4">Assurez-vous d'utiliser un mot de passe fort d'au moins 8 caractères.</p>

                            <form id="password-change-form" novalidate>
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label for="current-password" class="form-label">Mot de passe actuel *</label>
                                        <input type="password" class="form-control" id="current-password" autocomplete="current-password" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="new-password" class="form-label">Nouveau mot de passe *</label>
                                        <input type="password" class="form-control" id="new-password" autocomplete="new-password" required minlength="8">
                                        <div class="form-text">Minimum 8 caractères.</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="confirm-password" class="form-label">Confirmer le nouveau mot de passe *</label>
                                        <input type="password" class="form-control" id="confirm-password" autocomplete="new-password" required>
                                    </div>
                                </div>
                                <div id="password-change-error" class="alert alert-danger" style="display:none;" role="alert"></div>
                                <div id="password-change-success" class="alert alert-success" style="display:none;" role="status"></div>
                                <button type="submit" class="btn btn-primary btn-save-password d-flex align-items-center">
                                    <i class="fas fa-key me-2" aria-hidden="true"></i> Mettre à jour le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="modal" tabindex="-1" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notification</h5>
                    <button type="button" class="btn-close" id="close-modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modal-message"></p>
                    <div id="modal-user-details" style="display: none;">
                        <p><strong>Nom:</strong> <span id="modal-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                        <p><strong>Rôles:</strong> <span id="modal-roles"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="close-modal-btn">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</div>

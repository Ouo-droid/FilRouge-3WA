<?php
// Fichier : views/user/_form_add_user.php
// Partiel — formulaire d'ajout / édition d'un utilisateur
// Inclus dans users.php
?>

<div class="users-right">
    <div class="users-panel" id="users-form-panel">
        <div class="users-panel-title" id="users-panel-title">
            <i class="fas fa-user-plus" aria-hidden="true" id="users-panel-icon"></i>
            <span id="users-panel-title-text">Ajouter un utilisateur</span>
        </div>

        <form action="javascript:void(0);" method="POST" id="form-add-user"
              data-mode="add" data-edit-user-id="" onsubmit="return false;">

            <div class="users-form-group">
                <label for="firstname">Prénom *</label>
                <input type="text" name="firstname" id="firstname"
                       placeholder="Jean" required autocomplete="given-name" />
            </div>

            <div class="users-form-group">
                <label for="lastname">Nom *</label>
                <input type="text" name="lastname" id="lastname"
                       placeholder="Dupont" required autocomplete="family-name" />
            </div>

            <div class="users-form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email"
                       placeholder="jean.dupont@example.com" required autocomplete="email" />
            </div>

            <div class="users-form-group" id="role-group">
                <label for="role-select">Rôle *</label>
                <select name="roleId" id="role-select" required>
                    <option value="">Chargement des rôles...</option>
                </select>
            </div>

            <!-- Champ mot de passe + générateur -->
            <div class="users-form-group" id="password-group">
                <label for="password" id="password-label">Mot de passe *</label>

                <div class="pwd-field-wrapper">
                    <input type="password" name="password" id="password"
                           placeholder="••••••••" required autocomplete="new-password" />
                    <button type="button" class="pwd-toggle-btn" id="pwd-toggle"
                            aria-label="Afficher / masquer le mot de passe">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="pwd-generator">
                    <div class="pwd-generator-controls">
                        <span class="pwd-length-label">
                            Longueur&nbsp;: <strong id="pwd-length-display">12</strong> car.
                        </span>
                        <input type="range" id="pwd-length" min="8" max="32" value="12"
                               class="pwd-length-slider" aria-label="Nombre de caractères" />
                    </div>
                    <button type="button" class="pwd-generate-btn" id="pwd-generate">
                        <i class="fas fa-dice" aria-hidden="true"></i> Générer
                    </button>
                </div>
            </div>

            <button type="submit" class="users-form-submit" id="users-form-submit">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Ajouter l'utilisateur
            </button>

            <button type="button" class="users-form-cancel" id="users-form-cancel" style="display:none;">
                Annuler
            </button>
        </form>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?>">
(function () {
    const pwdInput      = document.getElementById('password');
    const pwdToggleBtn  = document.getElementById('pwd-toggle');
    const pwdGenerateBtn= document.getElementById('pwd-generate');
    const pwdLengthSlider = document.getElementById('pwd-length');
    const pwdLengthDisplay = document.getElementById('pwd-length-display');

    if (!pwdInput || !pwdToggleBtn || !pwdGenerateBtn || !pwdLengthSlider) return;

    // ── Afficher / masquer le mot de passe ───────────────────────────────────
    pwdToggleBtn.addEventListener('click', function () {
        const isHidden = pwdInput.type === 'password';
        pwdInput.type  = isHidden ? 'text' : 'password';
        this.querySelector('i').className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
        this.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });

    // ── Mise à jour de l'affichage de la longueur ───────────────────────────
    pwdLengthSlider.addEventListener('input', function () {
        pwdLengthDisplay.textContent = this.value;
    });

    // ── Générateur de mot de passe (CSPRNG) ─────────────────────────────────
    pwdGenerateBtn.addEventListener('click', function () {
        const len     = parseInt(pwdLengthSlider.value, 10);
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*()-_=+[]{}';
        const array   = new Uint32Array(len);
        window.crypto.getRandomValues(array);

        let pwd = '';
        for (let i = 0; i < len; i++) {
            pwd += charset[array[i] % charset.length];
        }

        pwdInput.value = pwd;
        pwdInput.type  = 'text';
        pwdToggleBtn.querySelector('i').className = 'fas fa-eye-slash';
        pwdToggleBtn.setAttribute('aria-label', 'Masquer le mot de passe');
    });
})();
</script>

<div class="login-page">
    <!-- Logo -->
    <div class="login-logo" aria-hidden="true">
        <i class="fas fa-cube me-2" aria-hidden="true"></i>
        <span>KENTEC</span>
    </div>

    <!-- Register Card -->
    <div class="login-card" role="main">
        <h1 class="login-title mt-3">Créer un compte</h1>

        <?php if (isset($message)) { ?>
            <div class="login-error" role="alert" aria-live="polite"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8', false); ?></div>
        <?php } ?>

        <form method="post" action="" novalidate aria-label="Formulaire de création de compte">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="login-field">
                <label for="firstname">Prénom <span aria-hidden="true">*</span></label>
                <input type="text" id="firstname" name="firstname" placeholder="Entrez votre prénom" autocomplete="given-name" required aria-required="true" />
            </div>

            <div class="login-field">
                <label for="lastname">Nom <span aria-hidden="true">*</span></label>
                <input type="text" id="lastname" name="lastname" placeholder="Entrez votre nom" autocomplete="family-name" required aria-required="true" />
            </div>

            <div class="login-field">
                <label for="email">Adresse email <span aria-hidden="true">*</span></label>
                <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" autocomplete="email" required aria-required="true" />
            </div>

            <div class="login-field">
                <label for="pwd">Mot de passe <span aria-hidden="true">*</span></label>
                <div class="login-password-wrapper">
                    <input type="password" id="pwd" name="password" placeholder="Minimum 8 caractères" autocomplete="new-password" minlength="8" required aria-required="true" aria-describedby="pwd-hint" />
                    <button type="button" class="login-toggle-password" aria-label="Afficher le mot de passe" aria-pressed="false">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
                <span id="pwd-hint" class="visually-hidden">Minimum 8 caractères requis</span>
            </div>

            <div class="login-field">
                <label for="confirm_pwd">Confirmer le mot de passe <span aria-hidden="true">*</span></label>
                <div class="login-password-wrapper">
                    <input type="password" id="confirm_pwd" name="confirm_password" placeholder="Répétez votre mot de passe" autocomplete="new-password" minlength="8" required aria-required="true" />
                    <button type="button" class="login-toggle-password" aria-label="Afficher la confirmation du mot de passe" aria-pressed="false">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="login-field">
                <label for="role_id">Rôle <span aria-hidden="true">*</span></label>
                <select name="role_id" id="role_id" class="login-select" required aria-required="true">
                    <?php foreach ($roles ?? [] as $role): ?>
                        <option value="<?= htmlspecialchars($role->getId(), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($role->getName(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="login-submit">Créer le compte</button>
        </form>

        <div class="login-options" style="justify-content: center; margin-top: 20px;">
            <a href="/login" class="login-forgot">Déjà un compte ? Se connecter</a>
        </div>
    </div>
</div>

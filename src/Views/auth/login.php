<div class="login-page">
    <!-- Logo -->
    <div class="login-logo" aria-hidden="true">
        <i class="fas fa-cube me-2" aria-hidden="true"></i>
        <span>KENTEC</span>
    </div>

    <!-- Login Card -->
    <div class="login-card" role="main">
        <h1 class="login-title mt-3">Connexion</h1>

        <?php if (isset($message)) { ?>
            <div class="login-error" role="alert" aria-live="polite"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8', false); ?></div>
        <?php } ?>

        <form method="post" action="" novalidate aria-label="Formulaire de connexion">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="login-field">
                <label for="login">Adresse email</label>
                <input type="email" id="login" name="email" placeholder="Entrez votre adresse email de compte" autocomplete="email" required aria-required="true" />
            </div>

            <div class="login-field">
                <label for="pwd">Mot de passe</label>
                <div class="login-password-wrapper">
                    <input type="password" id="pwd" name="password" placeholder="Entrez votre mot de passe" autocomplete="current-password" required aria-required="true" />
                    <button type="button" class="login-toggle-password" aria-label="Afficher le mot de passe" aria-pressed="false">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="login-options">
                <a href="#" class="login-forgot">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="login-submit">Connexion</button>
        </form>
    </div>
</div>

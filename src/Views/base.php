<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' — KenTec' : 'KenTec'; ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon-KenTec.ico">

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= htmlspecialchars($_csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <!-- Custom Style Bundle -->
    <link rel="stylesheet" href="/build/app.bundle.css?v=<?php echo time(); ?>">

    <!-- JavaScript Bundle -->
    <script src="/build/app.bundle.js?v=<?php echo time(); ?>" nonce="<?php echo CSP_NONCE; ?>" defer></script>
</head>
<?php
    // Determine si la page a besoin de la sidebar
    // Les pages d'auth (login, register) et les pages de test de role n'ont pas de sidebar
    $isAuthPage = false !== strpos($view, 'auth/') || false !== strpos($view, 'auth\\');
    $isTestPage = 'roleAdmin.php' === basename($view) || 'roleUser.php' === basename($view);
    $showSidebar = !$isAuthPage && !$isTestPage;

    // Hide AppBar on settings page (as requested)
    $isSettingsPage = false !== strpos($view, 'settings/') || false !== strpos($view, 'settings\\');
    $showAppbar = !$isSettingsPage;
    ?>
<body class="dashboard">
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>
    <?php if ($showSidebar) { ?>
        <!-- Sidebar partagee  -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        <?php include __DIR__ . '/partials/mobile_header.php'; ?>

        <!-- Contenu principal (equivalent du <router-outlet> Angular) -->
        <main id="main-content" class="app-content">
            <?php if ($showAppbar) { ?>
                <?php include __DIR__ . '/partials/appbar.php'; ?>
            <?php } ?>
            <?php include_once $view; ?>
        </main>
    <?php } else { ?>
        <main id="main-content">
            <?php include_once $view; ?>
        </main>
    <?php } ?>

    <?php if ($showSidebar) { ?><footer class="visually-hidden" aria-label="Pied de page">© <?php echo date('Y'); ?> KenTec</footer><?php } ?>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js" nonce="<?php echo CSP_NONCE; ?>"></script>
</body>
</html>
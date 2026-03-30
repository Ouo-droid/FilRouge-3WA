<header class="appbar" role="banner">
    <div class="appbar-content">
        <?php
        $user = Kentec\Kernel\Security\Security::getUser();
        $userName  = $user ? htmlspecialchars($user->getFirstname() . ' ' . $user->getLastname()) : 'Utilisateur';
        $initials  = $user ? strtoupper(substr($user->getFirstname() ?? '', 0, 1) . substr($user->getLastname() ?? '', 0, 1)) : '??';
        $userRole  = $user ? ($user->getRoleName() ?? 'USER') : 'USER';
        $roleLabel = match($userRole) {
            'PDG'   => 'PDG',
            'ADMIN' => 'Administrateur',
            'CDP'   => 'Chef de projet',
            'USER'  => 'Collaborateur',
            default => htmlspecialchars($userRole),
        };
        $roleSlug  = strtolower($userRole);
        ?>
        <div class="appbar-avatar me-3" aria-label="Avatar de <?php echo $userName; ?>" role="img"><?php echo $initials; ?></div>
        <div class="appbar-info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <p class="appbar-username mb-0"><?php echo $userName; ?></p>
                <span class="appbar-role-badge appbar-role-<?php echo $roleSlug; ?>"><?php echo $roleLabel; ?></span>
            </div>
            <p class="mb-0 text-muted">Finissons-en avec vos tâches du jour !</p>
        </div>
    </div>
</header>

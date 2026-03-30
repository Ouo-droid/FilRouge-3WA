<?php
$currentUri  = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentUri, PHP_URL_PATH);
$currentUser = \Kentec\Kernel\Security\Security::getUser();
$userRole    = $currentUser ? ($currentUser->getRoleName() ?? 'USER') : 'USER';

function isActive($path, $currentPath)
{
    if ('/' === $path && '/' === $currentPath) return 'active';
    if ('/' !== $path && 0 === strpos($currentPath, $path)) return 'active';
    return '';
}
?>
<div class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <!-- Logo -->
        <div class="sidebar-logo d-none d-lg-flex">
            <img src="/images/Logo-KenTec.png" alt="KenTec" style="max-width: 55%; height: auto;">
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav mt-10" aria-label="Navigation principale">

            <?php /* ── Dashboard : tous les rôles ── */ ?>
            <?php $active = isActive('/', $currentPath); ?>
            <a class="sidebar-link <?php echo $active; ?>" href="/" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <span lang="en">Dashboard</span>
            </a>

            <?php /* ── ADMIN : utilisateurs en premier ── */ ?>
            <?php if ($userRole === 'ADMIN' || $userRole === 'PDG') : ?>
                <?php $active = isActive('/users', $currentPath); ?>
                <a class="sidebar-link <?php echo $active; ?>" href="/users" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                    <i class="fa-solid fa-users-cog" aria-hidden="true"></i>
                    <span>Utilisateurs</span>
                </a>
            <?php endif; ?>

            <?php /* ── Projets : tous les rôles (lecture seule pour USER) ── */ ?>
            <?php $active = isActive('/projects', $currentPath); ?>
            <a class="sidebar-link <?php echo $active; ?>" href="/projects" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                <i class="fa-regular fa-folder" aria-hidden="true"></i>
                <span>Projets</span>
            </a>

            <?php /* ── Tâches : tous les rôles ── */ ?>
            <?php $active = isActive('/tasks', $currentPath); ?>
            <a class="sidebar-link <?php echo $active; ?>" href="/tasks" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                <span><?php echo $userRole === 'USER' ? 'Mes tâches' : 'Tâches'; ?></span>
            </a>

            <?php /* ── Clients : tous sauf USER ── */ ?>
            <?php if ($userRole !== 'USER') : ?>
                <?php $active = isActive('/clients', $currentPath); ?>
                <a class="sidebar-link <?php echo $active; ?>" href="/clients" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                    <i class="fa-regular fa-address-book" aria-hidden="true"></i>
                    <span>Clients</span>
                </a>
            <?php endif; ?>

            <?php /* ── Équipe : ADMIN, CDP, PDG ── */ ?>
            <?php if ($userRole === 'ADMIN' || $userRole === 'CDP' || $userRole === 'PDG') : ?>
                <?php $active = isActive('/team', $currentPath); ?>
                <a class="sidebar-link <?php echo $active; ?>" href="/team" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    <span>Équipe</span>
                </a>
            <?php endif; ?>

            <!-- <?php /* ── Message : optionnel (désactivé) ── */ ?>
            <a class="sidebar-link" href="#messages" aria-disabled="true" tabindex="-1" onclick="return false;">
                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                <span>Message<br><small>(optionnel)</small></span>
            </a> -->

            <?php /* ── Paramètres : tous les rôles ── */ ?>
            <?php $active = isActive('/settings', $currentPath); ?>
            <a class="sidebar-link <?php echo $active; ?>" href="/settings" <?php echo $active ? 'aria-current="page"' : ''; ?>>
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                <span>Paramètres</span>
            </a>

        </nav>

        <!-- Déconnexion -->
        <div class="sidebar-logout">
            <a href="/logout" class="sidebar-link logout-link">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>
</div>

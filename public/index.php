<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Configure session parameters before starting
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    // 'domain'   => 'your-domain.com', // Set in production
    'secure'   => ($_ENV['APP_ENV'] ?? 'development') === 'production',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Security Headers
$nonce = base64_encode(random_bytes(16));
define('CSP_NONCE', $nonce);

header('Content-Type: text/html; charset=utf-8');
$csp = "default-src 'self' https://cdnjs.cloudflare.com; ";
$csp .= "script-src 'nonce-" . $nonce . "' 'strict-dynamic' https://cdnjs.cloudflare.com; ";
$csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; ";
$csp .= "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; ";
$csp .= "img-src 'self' data: https:; ";
$csp .= "connect-src 'self' https://cdnjs.cloudflare.com; ";
$csp .= "object-src 'none'; ";
$csp .= "base-uri 'self'; ";
$csp .= "form-action 'self';";
$csp .= "frame-ancestors 'none';";

header('Content-Security-Policy: ' . $csp);
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// Empêche le clickjacking : le navigateur refuse d'afficher la page dans une iframe.
// frame-ancestors 'none' dans le CSP couvre les navigateurs modernes.
// X-Frame-Options couvre les navigateurs plus anciens qui ignorent le CSP.
header('X-Frame-Options: DENY');

// Servir les fichiers statiques directement
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '';

if (preg_match('/\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    $filePath = __DIR__ . $path;

    if (file_exists($filePath)) {
        $mimeTypes = [
            'js'   => 'application/javascript',
            'css'  => 'text/css',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'ico'  => 'image/x-icon',
            'svg'  => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'  => 'font/ttf',
            'eot'  => 'application/vnd.ms-fontobject',
        ];

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found';
        exit;
    }
}

use Kentec\Kernel\Configuration;
use Kentec\Kernel\Kernel;

Configuration::loadConfiguration();

if (!isset($_ENV['CONTROLLER_NAMESPACE'])) {
    $_ENV['CONTROLLER_NAMESPACE'] = 'Kentec\App\Controller\\';
}
if (!isset($_ENV['DEBUG'])) {
    $_ENV['DEBUG'] = 'true';
}

try {
    Kernel::boot();
} catch (\Throwable $e) {
    if ($e->getMessage() === 'Unauthorized') {
        header('Location: /login');
        exit;
    }

    if ($e->getMessage() === 'No route found') {
        http_response_code(404);
        include __DIR__ . '/../src/Views/errors/404.php';
        exit;
    }

    // 403 Forbidden : l'utilisateur est connecté mais son rôle ne lui donne pas accès
    if ($e->getMessage() === 'Forbidden') {
        http_response_code(403);
        include __DIR__ . '/../src/Views/errors/403.php';
        exit;
    }

    // Mode debug : afficher le détail de l'erreur uniquement si DEBUG=true dans .env
    if ('false' === $_ENV['DEBUG']) {
        http_response_code(500);
        include __DIR__ . '/../src/Views/errors/500.php';
        exit;
    } else {
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $code = $e->getCode();
        $trace = $e->getTraceAsString();
        include_once __DIR__ . '/../kernel/Error/debugger.php';
        exit(0);
    }
}

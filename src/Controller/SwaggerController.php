<?php

declare(strict_types=1);

namespace Kentec\App\Controller;

use Kentec\Kernel\Http\AbstractController;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[OA\Info(title: 'KenTec Fil-Rouge API', version: '1.0.0')]
#[OA\Server(url: '/', description: 'Relative server path')]
#[OA\SecurityScheme(
    securityScheme: 'cookieAuth',
    type: 'apiKey',
    in: 'cookie',
    name: 'jwt_token'
)]
#[OA\OpenApi(
    security: [['cookieAuth' => []]]
)]
class SwaggerController extends AbstractController
{
    /**
     * Affiche l'interface Swagger UI
     */
    public function index(): void
    {
        $view = __DIR__ . '/../../src/Views/swagger/index.php';
        if (!file_exists($view)) {
            throw new \InvalidArgumentException("Vue introuvable : {$view}");
        }
        include $view;
        exit;
    }

    /**
     * Génère et renvoie le JSON OpenAPI
     */
    public function getApiDocs(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        if (!class_exists('OpenApi\Generator')) {
            throw new \Exception('Swagger library not found. Please run composer install.');
        }
        $generator = new Generator();
        $openapi = $generator->generate([__DIR__ . '/../../src']);
        header('Content-Type: application/json');
        echo $openapi->toJson();
        exit;
    }
}

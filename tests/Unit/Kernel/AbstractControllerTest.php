<?php

namespace Kentec\Tests\Unit\Kernel;

use PHPUnit\Framework\TestCase;
use Kentec\Kernel\Utils\AbstractController;

/**
 * Tests unitaires pour AbstractController.
 * On teste les méthodes de validation (path traversal, open redirect)
 * sans déclencher les headers HTTP (qui ne marchent pas en CLI).
 */
class AbstractControllerTest extends TestCase
{
    /**
     * Teste que render() rejette les chemins contenant ".." (path traversal).
     * Un attaquant pourrait essayer "../../etc/passwd" pour lire des fichiers système.
     */
    public function testRenderRejectsPathTraversal(): void
    {
        $controller = new class extends AbstractController {};

        $this->expectException(\RuntimeException::class);
        $controller->render('../../etc/passwd');
    }

    /**
     * Teste que render() rejette les chemins avec double-point même au milieu.
     */
    public function testRenderRejectsHiddenPathTraversal(): void
    {
        $controller = new class extends AbstractController {};

        $this->expectException(\RuntimeException::class);
        $controller->render('home/../../../etc/passwd');
    }

    /**
     * Teste que redirect() rejette les URLs externes (open redirect).
     * Un attaquant pourrait essayer "https://evil.com" pour rediriger les utilisateurs.
     */
    public function testRedirectRejectsExternalUrls(): void
    {
        $controller = new class extends AbstractController {};

        $this->expectException(\InvalidArgumentException::class);
        $controller->redirect('https://evil.com');
    }

    /**
     * Teste que redirect() rejette les URLs avec double-slash (protocol-relative).
     * "//evil.com" est interprété par les navigateurs comme "https://evil.com".
     */
    public function testRedirectRejectsProtocolRelativeUrls(): void
    {
        $controller = new class extends AbstractController {};

        $this->expectException(\InvalidArgumentException::class);
        $controller->redirect('//evil.com');
    }
}

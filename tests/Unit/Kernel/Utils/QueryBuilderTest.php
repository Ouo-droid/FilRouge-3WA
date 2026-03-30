<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Kernel\Utils;

use Kentec\Kernel\Database\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testBasicSelect()
    {
        $qb = new QueryBuilder('users');
        $qb->select(['id', 'username']);

        [$sql, $params] = $qb->build();

        $this->assertEquals('SELECT id, username FROM users', $sql);
        $this->assertEmpty($params);
    }

    public function testWhereEquals()
    {
        $qb = new QueryBuilder('users');
        $qb->whereEquals('id', 1);

        [$sql, $params] = $qb->build();

        $this->assertStringContainsString('WHERE id = :eq_', $sql);
        $this->assertCount(1, $params);
        $this->assertEquals(1, reset($params));
    }

    public function testWhereLike()
    {
        $qb = new QueryBuilder('users');
        $qb->whereLike(['username', 'email'], 'admin');

        [$sql, $params] = $qb->build();

        $this->assertStringContainsString('WHERE (username LIKE :like_', $sql);
        $this->assertStringContainsString('OR email LIKE :like_', $sql);
        $this->assertCount(2, $params);
        $this->assertEquals('%admin%', reset($params));
    }

    public function testOrderByAndLimit()
    {
        $qb = new QueryBuilder('users');
        $qb->orderBy('created_at', 'DESC')
           ->limit(10, 20);

        [$sql, $params] = $qb->build();

        $this->assertStringContainsString('ORDER BY created_at DESC', $sql);
        $this->assertStringContainsString('LIMIT 10 OFFSET 20', $sql);
    }

    public function testPaginate()
    {
        $qb = new QueryBuilder('users');
        $qb->paginate(2, 15);

        [$sql, $params] = $qb->build();

        $this->assertStringContainsString('LIMIT 15 OFFSET 15', $sql);
    }

    /**
     * whereILike doit générer des conditions ILIKE sur chaque colonne, reliées par OR.
     *
     * ILIKE est l'opérateur PostgreSQL insensible à la casse.
     * Les valeurs doivent être encadrées par des % pour une recherche "contient".
     */
    public function testWhereILike(): void
    {
        $qb = new QueryBuilder('client');
        $qb->whereILike(['companyname', 'workfield'], 'tech');

        [$sql, $params] = $qb->build();

        // La clause WHERE doit contenir ILIKE sur les deux colonnes, liées par OR
        $this->assertStringContainsString('companyname ILIKE :ilike_', $sql);
        $this->assertStringContainsString('OR workfield ILIKE :ilike_', $sql);

        // Deux paramètres (un par colonne)
        $this->assertCount(2, $params);

        // Chaque valeur doit être encadrée par des %
        foreach ($params as $value) {
            $this->assertEquals('%tech%', $value);
        }
    }

    /**
     * whereILike avec une valeur vide ne doit ajouter aucune condition WHERE.
     *
     * Si l'utilisateur envoie une chaîne vide, on ne filtre pas.
     */
    public function testWhereILikeAvecValeurVideNAjouteRien(): void
    {
        $qb = new QueryBuilder('client');
        $qb->whereILike(['companyname', 'workfield'], '');

        [$sql, $params] = $qb->build();

        $this->assertStringNotContainsString('WHERE', $sql);
        $this->assertEmpty($params);
    }

    /**
     * whereILike avec un tableau de colonnes vide ne doit ajouter aucune condition.
     */
    public function testWhereILikeAvecColonnesVidesNAjouteRien(): void
    {
        $qb = new QueryBuilder('client');
        $qb->whereILike([], 'tech');

        [$sql, $params] = $qb->build();

        $this->assertStringNotContainsString('WHERE', $sql);
        $this->assertEmpty($params);
    }

    /**
     * Combinaison whereEquals + whereILike : les deux conditions doivent être jointes par AND.
     *
     * Exemple concret : isactive = 'true' AND (companyname ILIKE '%tech%' OR siret ILIKE '%tech%')
     */
    public function testWhereEqualsEtWhereILikeSontJointsParAnd(): void
    {
        $qb = new QueryBuilder('client');
        $qb->whereEquals('isactive', 'true')
           ->whereILike(['companyname', 'siret'], 'tech');

        [$sql, $params] = $qb->build();

        // Les deux blocs de conditions doivent être présents
        $this->assertStringContainsString('isactive = :eq_', $sql);
        $this->assertStringContainsString('ILIKE :ilike_', $sql);

        // Trois paramètres au total : 1 pour isactive + 2 pour les ILIKE
        $this->assertCount(3, $params);
    }
}

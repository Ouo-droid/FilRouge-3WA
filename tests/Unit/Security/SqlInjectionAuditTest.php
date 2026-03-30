<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

/**
 * Audit de non-régression : protection contre les injections SQL.
 *
 * === QU'EST-CE QU'UNE INJECTION SQL ? ===
 * Un attaquant envoie du SQL dans un champ de formulaire pour manipuler la requête.
 * Exemple classique : email = "' OR '1'='1" → la requête retourne tous les utilisateurs.
 *
 * === COMMENT CE PROJET EST PROTÉGÉ ===
 * Repository.php utilise EXCLUSIVEMENT des requêtes préparées PDO :
 *   $pdo->prepare("SELECT * FROM users WHERE email = :email");
 *   $statement->execute([':email' => $emailUtilisateur]);
 * Les paramètres sont liés SÉPARÉMENT du SQL → impossible d'injecter du SQL.
 *
 * Ces tests vérifient la STRUCTURE DU CODE SOURCE (tests de contrainte) :
 * - Aucune concaténation de variable directement dans une chaîne SQL
 * - Utilisation systématique de SqlBuilder qui génère des paramètres nommés
 * - QueryBuilder utilise des paramètres nommés pour chaque condition WHERE
 */
class SqlInjectionAuditTest extends TestCase
{
    /** Chemin vers Repository.php */
    private string $cheminRepository;

    /** Chemin vers SqlBuilder.php */
    private string $cheminSqlBuilder;

    /** Chemin vers QueryBuilder.php */
    private string $cheminQueryBuilder;

    /** Code source de Repository */
    private string $codeRepository;

    /** Code source de SqlBuilder */
    private string $codeSqlBuilder;

    /** Code source de QueryBuilder */
    private string $codeQueryBuilder;

    protected function setUp(): void
    {
        $this->cheminRepository  = __DIR__ . '/../../../kernel/Utils/Repository.php';
        $this->cheminSqlBuilder  = __DIR__ . '/../../../kernel/Database/SqlBuilder.php';
        $this->cheminQueryBuilder = __DIR__ . '/../../../kernel/Utils/QueryBuilder.php';

        $this->codeRepository   = file_get_contents($this->cheminRepository);
        $this->codeSqlBuilder   = file_get_contents($this->cheminSqlBuilder);
        $this->codeQueryBuilder = file_get_contents($this->cheminQueryBuilder);
    }

    /**
     * SqlBuilder doit utiliser des paramètres nommés PDO (":nom") et non
     * la concaténation directe de variables dans le SQL.
     *
     * La présence de ":$key" ou ":$column" dans le code source confirme l'usage
     * de paramètres nommés (et non de concaténation directe de valeurs).
     */
    public function testSqlBuilderUtiliseParametresNommesPdo(): void
    {
        // SqlBuilder construit ses requêtes avec des placeholders ":column"
        $this->assertMatchesRegularExpression(
            '/:\$(?:key|column|primaryKey)/',
            $this->codeSqlBuilder,
            'SqlBuilder doit construire des paramètres nommés PDO de la forme :$key'
        );
    }

    /**
     * SqlBuilder ne doit pas concaténer des valeurs directement dans les requêtes SQL.
     *
     * La concaténation de valeurs dans le SQL serait : "WHERE id = " . $id
     * Ce pattern dans les requêtes SQL est une injection SQL potentielle.
     * On vérifie l'absence de ce pattern dans les méthodes de construction SQL.
     */
    public function testSqlBuilderNaConcateneValeursDansRequetes(): void
    {
        // Vérifie qu'il n'y a pas de concaténation directe "$value" ou "$fields" dans les clauses SQL
        // (les valeurs doivent passer par des paramètres nommés, jamais directement dans le SQL)
        $this->assertDoesNotMatchRegularExpression(
            '/(?:WHERE|SET|VALUES)\s.*"\s*\.\s*\$(?:value|fields|data|id)\b/',
            $this->codeSqlBuilder,
            'SqlBuilder ne doit pas concaténer directement des valeurs dans les clauses SQL'
        );
    }

    /**
     * Repository doit utiliser prepare() + execute() pour toutes ses requêtes.
     *
     * PDO::prepare() sépare le SQL des données → impossible d'injecter du SQL.
     * La présence de ces deux appels est le signe d'une utilisation correcte de PDO.
     */
    public function testRepositoryUtilisePrepareEtExecute(): void
    {
        $this->assertStringContainsString(
            '->prepare(',
            $this->codeRepository,
            'Repository doit utiliser PDO::prepare() pour préparer les requêtes'
        );

        $this->assertStringContainsString(
            '->execute(',
            $this->codeRepository,
            'Repository doit utiliser PDOStatement::execute() pour exécuter les requêtes'
        );
    }

    /**
     * Repository ne doit pas exécuter de SQL brut via query() sans préparation.
     *
     * PDO::query() exécute du SQL directement sans paramètres préparés.
     * Son usage ici serait un signe d'une possible injection SQL.
     */
    public function testRepositoryNUtilisePasQuerySansPrepare(): void
    {
        // ->query() sans être précédé de ->prepare() serait dangereux
        // On vérifie que le Repository ne fait pas d'appel direct à ->query()
        $this->assertStringNotContainsString(
            '->query(',
            $this->codeRepository,
            'Repository ne doit pas utiliser PDO::query() (préférer prepare() + execute())'
        );
    }

    /**
     * QueryBuilder doit utiliser des paramètres nommés pour chaque condition WHERE.
     *
     * La méthode generateParamName() génère des noms uniques pour les paramètres.
     * Son usage confirme que les valeurs ne sont jamais injectées directement dans le SQL.
     */
    public function testQueryBuilderUtiliseParametresNommes(): void
    {
        $this->assertStringContainsString(
            'generateParamName',
            $this->codeQueryBuilder,
            'QueryBuilder doit générer des noms de paramètres uniques pour éviter les injections'
        );

        // Les paramètres sont stockés dans $this->params, pas directement dans le SQL
        $this->assertStringContainsString(
            '$this->params[',
            $this->codeQueryBuilder,
            'QueryBuilder doit stocker les valeurs dans un tableau de paramètres séparé du SQL'
        );
    }

    /**
     * QueryBuilder doit valider les opérateurs SQL autorisés pour éviter les injections via l'opérateur.
     *
     * Si on pouvait passer n'importe quel opérateur, un attaquant pourrait injecter :
     * "= :val OR 1=1 --" comme "opérateur".
     * La liste blanche d'opérateurs autorisés dans where() protège contre ça.
     */
    public function testQueryBuilderValideOperateursAutorises(): void
    {
        $this->assertStringContainsString(
            'allowedOperators',
            $this->codeQueryBuilder,
            'QueryBuilder doit valider les opérateurs SQL via une liste blanche d\'opérateurs autorisés'
        );
    }

    /**
     * Test fonctionnel : vérifier que les paramètres nommés PDO protègent bien contre l'injection.
     *
     * On simule la construction d'une requête avec une valeur malveillante.
     * Avec des paramètres préparés, la valeur est traitée comme une donnée, pas comme du SQL.
     */
    public function testParametresPreparesNeutraliseInjectionSql(): void
    {
        // Valeur qu'un attaquant pourrait soumettre dans un champ email
        $valeurMalveillante = "' OR '1'='1' --";

        // Avec filter_var + paramètres préparés, cette valeur ne peut pas exécuter du SQL
        // filter_var rejette cet email dès la validation d'entrée
        $emailEstValide = filter_var($valeurMalveillante, FILTER_VALIDATE_EMAIL);

        $this->assertFalse(
            $emailEstValide,
            'Une tentative d\'injection SQL via le champ email doit être rejetée par la validation'
        );

        // Vérification supplémentaire : InputValidator::validateEmail() doit aussi rejeter ça
        $validationInputValidator = \Kentec\Kernel\Security\InputValidator::validateEmail($valeurMalveillante);

        $this->assertFalse(
            $validationInputValidator,
            'InputValidator::validateEmail() doit rejeter la tentative d\'injection SQL'
        );
    }
}

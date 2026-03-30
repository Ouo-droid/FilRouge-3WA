<?php

namespace Kentec\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Kentec\App\Model\User;

/**
 * Tests unitaires pour le modèle User.
 *
 * On vérifie trois choses :
 * 1. Le mapping base → PHP (fromDatabaseArray) : les noms de colonnes SQL
 *    sont transformés en noms de propriétés PHP.
 * 2. Le mapping PHP → base (toDatabaseArray) : les données de l'objet User
 *    sont transformées en tableau SQL prêt à insérer/mettre à jour.
 * 3. Le mapping PHP → API (toArray) : les données exposées au frontend.
 */
class UserModelTest extends TestCase
{
    /**
     * Vérifie que fromDatabaseArray() mappe correctement les colonnes SQL
     * vers les noms de propriétés PHP (camelCase).
     *
     * Exemple : la colonne "role_id" en base devient "roleId" en PHP,
     * et "role_name" devient "roleName".
     */
    public function testFromDatabaseArrayMappeLesColonnesSqlVersPhp(): void
    {
        $ligneBaseDeDonnees = [
            'id'          => 'abc-123-def',
            'email'       => 'jean.dupont@example.com',
            'firstname'   => 'Jean',
            'lastname'    => 'Dupont',
            'role_id'     => 'role-uuid-456',
            'role_name'   => 'CDP',
            'fieldofwork' => 'Développement',
            'isactive'    => true,
        ];

        $donneesMappees = User::fromDatabaseArray($ligneBaseDeDonnees);

        // La colonne "role_id" doit devenir "roleId"
        $this->assertEquals('role-uuid-456', $donneesMappees['roleId'], 'role_id doit être mappé vers roleId');
        // La colonne "role_name" doit devenir "roleName"
        $this->assertEquals('CDP', $donneesMappees['roleName'], 'role_name doit être mappé vers roleName');
        // "fieldofwork" reste inchangé (pas de transformation)
        $this->assertEquals('Développement', $donneesMappees['fieldofwork'], 'fieldofwork ne doit pas être modifié');
        // Les clés originales doivent être supprimées après mapping
        $this->assertArrayNotHasKey('role_id', $donneesMappees, 'La clé SQL role_id doit être supprimée après mapping');
        $this->assertArrayNotHasKey('role_name', $donneesMappees, 'La clé SQL role_name doit être supprimée après mapping');
    }

    /**
     * Vérifie que le champ "degree" (tableau PostgreSQL) est correctement
     * désérialisé depuis la chaîne de caractères stockée en base.
     *
     * PostgreSQL stocke les tableaux sous forme de chaîne : {"PHP","Java"}
     * → PHP doit recevoir : ["PHP", "Java"]
     */
    public function testFromDatabaseArrayConvertitDegreeEnTableauPhp(): void
    {
        $ligneBaseDeDonnees = [
            'email'     => 'test@example.com',
            'firstname' => 'Alice',
            'lastname'  => 'Martin',
            'degree'    => '{"PHP","Java"}', // Format PostgreSQL
        ];

        $donneesMappees = User::fromDatabaseArray($ligneBaseDeDonnees);

        $this->assertIsArray($donneesMappees['degree'], 'degree doit être un tableau PHP, pas une chaîne');
        $this->assertContains('PHP', $donneesMappees['degree'], 'PHP doit être dans le tableau degree');
        $this->assertContains('Java', $donneesMappees['degree'], 'Java doit être dans le tableau degree');
    }

    /**
     * Vérifie que toDatabaseArray() produit les bonnes clés SQL (snake_case)
     * pour construire les requêtes INSERT et UPDATE.
     */
    public function testToDatabaseArrayProduitsLesBonnesClesSql(): void
    {
        $utilisateur = new User();
        $utilisateur->setFirstname('Marie');
        $utilisateur->setLastname('Curie');
        $utilisateur->setEmail('marie.curie@example.com');
        $utilisateur->setPassword('hash_argon2i_xyz');
        $utilisateur->setRoleId('role-uuid-admin');

        $tableauSql = $utilisateur->toDatabaseArray();

        // Vérification des clés attendues par la base de données
        $this->assertEquals('Marie', $tableauSql['firstname']);
        $this->assertEquals('Curie', $tableauSql['lastname']);
        $this->assertEquals('marie.curie@example.com', $tableauSql['email']);
        $this->assertEquals('hash_argon2i_xyz', $tableauSql['password']);
        // Le rôle doit être stocké avec la clé "role_id" (snake_case SQL)
        $this->assertEquals('role-uuid-admin', $tableauSql['role_id'], 'role_id doit être la clé SQL du rôle');
    }

    /**
     * Vérifie que toDatabaseArray() exclut les valeurs null.
     * Les champs null ne doivent pas être inclus dans les requêtes SQL.
     */
    public function testToDatabaseArrayExclutLesValeursNull(): void
    {
        $utilisateur = new User();
        $utilisateur->setEmail('test@example.com');
        $utilisateur->setFirstname('Test');
        $utilisateur->setLastname('User');
        // fieldofwork, jobtitle, degree, roleId → laissés à null

        $tableauSql = $utilisateur->toDatabaseArray();

        // fieldofwork est null → ne doit pas être dans le tableau SQL
        $this->assertArrayNotHasKey('fieldofwork', $tableauSql, 'fieldofwork null doit être exclu du tableau SQL');
        // role_id est null → ne doit pas être dans le tableau SQL
        $this->assertArrayNotHasKey('role_id', $tableauSql, 'role_id null doit être exclu du tableau SQL');
    }

    /**
     * Vérifie que toArray() contient les clés attendues par le frontend (camelCase).
     * C'est ce tableau qui est encodé en JSON et envoyé au navigateur.
     */
    public function testToArrayContientLesClesAttendusPourLApi(): void
    {
        $utilisateur = new User();
        $utilisateur->setId('user-uuid-789');
        $utilisateur->setFirstname('Pierre');
        $utilisateur->setLastname('Bernard');
        $utilisateur->setEmail('pierre@example.com');
        $utilisateur->setRoleName('ADMIN');

        $tableauApi = $utilisateur->toArray();

        // Les clés doivent être en camelCase pour le frontend
        $this->assertArrayHasKey('id', $tableauApi);
        $this->assertArrayHasKey('firstname', $tableauApi);
        $this->assertArrayHasKey('lastname', $tableauApi);
        $this->assertArrayHasKey('email', $tableauApi);
        $this->assertArrayHasKey('roleId', $tableauApi, 'La clé doit être roleId (camelCase) et non role_id');
        $this->assertArrayHasKey('roleName', $tableauApi, 'La clé doit être roleName (camelCase) et non role_name');

        // Le mot de passe NE DOIT PAS être exposé dans l'API
        $this->assertArrayNotHasKey('password', $tableauApi, 'Le mot de passe ne doit jamais être exposé dans toArray()');
    }

    /**
     * Vérifie que isactive est bien initialisé à true par défaut.
     * Un nouvel utilisateur est actif à sa création.
     */
    public function testNouvelUtilisateurEstActifParDefaut(): void
    {
        $utilisateur = new User();

        $this->assertTrue($utilisateur->getIsactive(), 'Un nouvel utilisateur doit être actif (isactive = true) par défaut');
    }

    /**
     * Vérifie que setIsactive(false) fonctionne correctement pour le soft delete.
     * C'est le mécanisme utilisé dans deleteApiUser().
     */
    public function testSetIsactiveFalseDesactiveUtilisateur(): void
    {
        $utilisateur = new User();
        $utilisateur->setIsactive(false);

        $this->assertFalse($utilisateur->getIsactive(), 'Après setIsactive(false), l\'utilisateur doit être inactif');
    }
}

<?php

declare(strict_types=1);

namespace Kentec\Tests\Unit\Kernel\Database;

use Kentec\Kernel\Database\SqlBuilder;
use PHPUnit\Framework\TestCase;

class TestUser
{
    public $id = 1;
    public $name = 'John Doe';
    public $email = 'john@example.com';
}

class SqlBuilderTest extends TestCase
{
    public function testPrepareInsert()
    {
        $user = new TestUser();
        $result = SqlBuilder::prepareInsert($user, 'users');

        $this->assertStringContainsString('INSERT INTO users', $result['sql']);
        $this->assertStringContainsString('(name, email)', $result['sql']);
        $this->assertArrayHasKey(':name', $result['values']);
        $this->assertEquals('John Doe', $result['values'][':name']);
    }

    public function testPrepareUpdate()
    {
        $user = new TestUser();
        $result = SqlBuilder::prepareUpdate($user, 'users');

        $this->assertStringContainsString('UPDATE users SET', $result['sql']);
        $this->assertStringContainsString('WHERE id = :id', $result['sql']);
        $this->assertEquals(1, $result['values'][':id']);
    }

    public function testSanitize()
    {
        $data = [
            'name' => '<script>alert("xss")</script>',
            'age' => 25,
            'active' => true,
            'date' => new \DateTime('2024-01-01 10:00:00'),
        ];

        $sanitized = SqlBuilder::sanitize($data);

        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $sanitized['name']);
        $this->assertEquals(25, $sanitized['age']);
        $this->assertEquals(1, $sanitized['active']);
        $this->assertEquals('2024-01-01 10:00:00', $sanitized['date']);
    }
}

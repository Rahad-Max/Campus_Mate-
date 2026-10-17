<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Database;

class DatabaseTest extends TestCase
{
    public function testDatabaseSingletonInstance(): void
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();

        $this->assertSame($db1, $db2, "Database::getInstance() must always return the exact same instance.");
    }

    public function testDatabasePdoConnection(): void
    {
        $pdo = Database::pdo();
        $this->assertInstanceOf(\PDO::class, $pdo, "Database::pdo() must return an active PDO instance.");
    }
}

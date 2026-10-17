<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Database;

class DatabaseConnectionTest extends TestCase
{
    public function testDatabaseTablesIntegrity(): void
    {
        $db = Database::getInstance();
        $tables = $db->fetchAll("SHOW TABLES");

        $this->assertNotEmpty($tables, "Database must contain tables.");
        $this->assertGreaterThanOrEqual(32, count($tables), "Database must contain all 32 planned tables.");
    }

    public function testDemoUsersSeeded(): void
    {
        $db = Database::getInstance();
        $userCount = (int)$db->fetch("SELECT COUNT(*) as cnt FROM users")['cnt'];

        $this->assertGreaterThan(0, $userCount, "Users table must contain seeded demonstration accounts.");
    }
}

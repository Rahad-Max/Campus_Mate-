<?php
namespace App\Core;

use PDO;
use PDOException;
use App\Exceptions\AppException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private function __construct()
    {
        $configPath = dirname(__DIR__, 2) . '/config/database.php';
        $config = file_exists($configPath) ? require $configPath : [];
        $dbConfig = $config['connections']['mysql'] ?? [];

        $host = $dbConfig['host'] ?? ($_ENV['DB_HOST'] ?? '127.0.0.1');
        $port = (int)($dbConfig['port'] ?? ($_ENV['DB_PORT'] ?? 3306));
        $database = $dbConfig['database'] ?? ($_ENV['DB_DATABASE'] ?? 'pub_campusmate');
        $username = $dbConfig['username'] ?? ($_ENV['DB_USERNAME'] ?? 'root');
        $password = $dbConfig['password'] ?? ($_ENV['DB_PASSWORD'] ?? '');
        $charset = $dbConfig['charset'] ?? ($_ENV['DB_CHARSET'] ?? 'utf8mb4');
        $collation = $dbConfig['collation'] ?? ($_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$collation}"
        ];

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new AppException(
                "Database Connection Error: " . $e->getMessage(),
                (int)$e->getCode(),
                $e,
                ['host' => $host, 'port' => $port, 'database' => $database]
            );
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function pdo(): PDO
    {
        return self::getInstance()->getConnection();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $res = $this->query($sql, $params)->fetch();
        return $res === false ? null : $res;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    // Prevent cloning and unserialization of singleton
    private function __clone() {}
    public function __wakeup()
    {
        throw new AppException("Cannot unserialize Database singleton.");
    }
}

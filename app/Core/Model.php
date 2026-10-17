<?php
namespace App\Core;

use PDO;
use App\Core\Database;
use App\Exceptions\AppException;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }

    public function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` = :val LIMIT 1";
        return $this->db->fetch($sql, [':val' => $value]);
    }

    public function all(string $orderBy = 'id DESC', int $limit = 500): array
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy} LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE'];
        if (!in_array($operator, $allowedOperators, true)) {
            $operator = '=';
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val";
        return $this->db->fetchAll($sql, [':val' => $value]);
    }

    public function create(array $data): int
    {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered)) {
            throw new AppException("No fillable attributes provided for {$this->table} insertion.");
        }

        $columns = array_keys($filtered);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";

        $params = [];
        foreach ($filtered as $key => $val) {
            $params[":{$key}"] = $val;
        }

        $this->db->query($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered)) {
            return false;
        }

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($filtered as $col => $val) {
            $setClauses[] = "`{$col}` = :{$col}";
            $params[":{$col}"] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $setClauses) . " WHERE `{$this->primaryKey}` = :id";
        return $this->db->execute($sql, $params) >= 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        return $this->db->execute($sql, [':id' => $id]) > 0;
    }

    public function count(string $whereClause = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($whereClause)) {
            $sql .= " WHERE " . $whereClause;
        }
        $res = $this->db->fetch($sql, $params);
        return (int)($res['total'] ?? 0);
    }
}

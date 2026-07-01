<?php

namespace App\Core\Model;

use PDO;
use PDOStatement;

class Database
{
    /**
     * @var Database|null 
     */
    private static ?Database $instance = null;

    /**
     * @var PDO 
     */
    private PDO $connection;

    /**
     * @var array 
     */
    private array $config;

    private function __construct()
    {
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? 'blog_app',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'pass' => $_ENV['DB_PASS'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
        ];

        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
        try {
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass']);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->connection->exec("SET NAMES {$this->config['charset']} COLLATE {$this->config['charset']}_general_ci");
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo "\033[33m⚠ Database `{$this->config['dbname']}` not found. Creating...\033[0m\n";
                $this->createDatabase();
            } else {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * @return Database|null
     */
    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return false|PDOStatement
     */
    public function runQuery(string $sql, array $params = []): bool|PDOStatement
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        
        return $statement;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->runQuery($sql, $params)->fetchAll();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function fetchOne(string $sql, array $params = []): mixed
    {
        return $this->runQuery($sql, $params)->fetch();
    }

    /**
     * @param string $table
     * @param array $data
     * @return false|string
     */
    public function insert(string $table, array $data)
    {
        if (isset($data[0]) && is_array($data[0])) {
            return $this->insertMultiple($table, $data);
        }

        return $this->insertSingle($table, $data);
    }

    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    private function insertSingle(string $table, array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->runQuery($sql, array_values($data));
        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $data
     * @return array
     */
    private function insertMultiple(string $table, array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $fields = array_keys($data[0]);
        $fieldCount = count($fields);

        $rowPlaceholders = '(' . implode(', ', array_fill(0, $fieldCount, '?')) . ')';

        $allPlaceholders = implode(', ', array_fill(0, count($data), $rowPlaceholders));

        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                VALUES {$allPlaceholders}";

        $params = [];
        foreach ($data as $row) {
            $params = array_merge($params, array_values($row));
        }

        $this->runQuery($sql, $params);

        $lastId = (int) $this->connection->lastInsertId();
        $ids = [];
        for ($i = 0; $i < count($data); $i++) {
            $ids[] = $lastId + $i;
        }

        return $ids;
    }

    /**
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @return void
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): void
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        $this->runQuery($sql, $params);
    }

    /**
     * @param string $table
     * @param string $where
     * @param array $params
     * @return void
     */
    public function delete(string $table, string $where, array $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->runQuery($sql, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool|PDOStatement
     */
    public function execute(string $sql, array $params = []): bool|PDOStatement
    {
        return $this->runQuery($sql, $params);
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    private function createDatabase()
    {
        try {
            // Подключаемся без указания базы
            $dsn = "mysql:host={$this->config['host']};charset={$this->config['charset']}";
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass']);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Создаём базу
            $dbName = $this->config['dbname'];
            $charset = $this->config['charset'];
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` 
                CHARACTER SET {$charset} COLLATE {$charset}_general_ci");

            $this->connection->query("use $dbName");
            echo "\033[32m✓ Database `{$dbName}` created successfully\033[0m\n";
        } catch (\PDOException $e) {
            echo "\033[31m✗ Failed to create database: " . $e->getMessage() . "\033[0m\n";
            exit(1);
        }
    }
}
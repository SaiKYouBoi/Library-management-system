<?php

namespace LibraryManagement\Repositories;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private PDO $connection;

    private function __construct(
        private string $host = 'localhost',
        private string $dbname = 'library_management',
        private string $username = 'saikyouboi',
        private string $password = 'ilias1234'
    ) {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
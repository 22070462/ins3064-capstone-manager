<?php

/**
 * Database Connection Class (Singleton Pattern)
 * 
 * This class implements the Singleton design pattern to ensure only one
 * database connection instance exists throughout the application lifecycle.
 * Uses PDO for secure database operations with prepared statements.
 * 
 * @package Config
 * @author  Capstone Project Team
 * @version 1.0.0
 */

namespace Config;

use PDO;
use PDOException;

class Database
{
    /**
     * Singleton instance of Database class
     * 
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * PDO connection object
     * 
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Database configuration
     */
    private string $host = 'localhost';
    private string $dbName = 'capstone_db';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation
     * Implements Singleton pattern
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Prevent cloning of the instance
     * 
     * @return void
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     * 
     * @return void
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance of Database
     * 
     * @return Database The singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Establish database connection using PDO
     * 
     * @return void
     * @throws PDOException If connection fails
     */
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log error in production, display in development
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the PDO connection object
     * 
     * @return PDO The active database connection
     */
    public function getConnection(): PDO
    {
        // Reconnect if connection is lost
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Test database connection
     * 
     * @return bool True if connection is active, false otherwise
     */
    public function testConnection(): bool
    {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Close the database connection
     * 
     * @return void
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }

    /**
     * Begin a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Get the last inserted ID
     * 
     * @return string The last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}

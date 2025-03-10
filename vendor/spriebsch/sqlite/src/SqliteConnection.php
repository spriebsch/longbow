<?php declare(strict_types=1);

/*
 * This file is part of Sqlite.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\sqlite;

use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

final class SqliteConnection implements Connection
{
    private ?SQLite3 $connection = null;
    private string   $database;

    public static function memory(): self
    {
        return new self(':memory:');
    }

    public static function from(string $database): self
    {
        return new self($database);
    }

    private function __construct(string $database)
    {
        $this->database = $database;
    }

    public function isInMemoryDatabase(): bool
    {
        return $this->database === ':memory:';
    }

    public function database(): string
    {
        return $this->database;
    }

    public function prepare(string $statement): SQLite3Stmt
    {
        return $this->connection()->prepare($statement);
    }

    public function exec(string $statement): bool
    {
        return $this->connection()->exec($statement);
    }

    public function query(string $query): SQLite3Result
    {
        return $this->connection()->query($query);
    }

    private function connection(): Sqlite3
    {
        if ($this->connection === null) {
            $this->connection = new SQLite3($this->database);
            $this->connection->enableExceptions(true);
        }

        return $this->connection;
    }
}

<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow;

use spriebsch\sqlite\Connection;
use spriebsch\sqlite\SqliteSchema;

final class LongbowDatabaseSchema extends SqliteSchema
{
    protected function schemaExists(Connection $connection): bool
    {
        $result = $connection->query("SELECT sql FROM sqlite_master WHERE name='positions'");
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            return false;
        }

        return $row['sql'] !== $this->sql();
    }

    protected function createSchema(Connection $connection): void
    {
        $connection->exec($this->sql());
    }

    private function sql(): string
    {
        return 'CREATE TABLE `positions` (
            `id` INTEGER PRIMARY KEY,
            `handlerId` TEXT UNIQUE,
            `eventId` TEXT,
            `timestamp` TEXT
        );';
    }
}

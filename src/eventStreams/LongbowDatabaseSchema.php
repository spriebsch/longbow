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
        $result = $connection->query(
            "SELECT COUNT(*) FROM sqlite_master
             WHERE type='table' AND name IN ('positions', 'processorFailures')",
        );

        return $result->fetchArray(SQLITE3_NUM)[0] === 2;
    }

    protected function createSchema(Connection $connection): void
    {
        $connection->exec($this->positionsSql());
        $connection->exec($this->processorFailuresSql());
    }

    private function positionsSql(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `positions` (
            `id` INTEGER PRIMARY KEY,
            `handlerId` TEXT UNIQUE,
            `eventId` TEXT,
            `timestamp` TEXT
        );';
    }

    private function processorFailuresSql(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `processorFailures` (
            `processorId` TEXT PRIMARY KEY,
            `eventId` TEXT,
            `failedAt` TEXT NOT NULL,
            `exceptionClass` TEXT NOT NULL,
            `exceptionMessage` TEXT NOT NULL
        );';
    }
}

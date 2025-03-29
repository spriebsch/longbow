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

use spriebsch\eventstore\EventId;
use spriebsch\sqlite\Connection;
use spriebsch\timestamp\Timestamp;
use spriebsch\uuid\UUID;
use SQLite3Stmt;
use const SQLITE3_TEXT;

final readonly class SqliteStreamPosition implements StreamPosition
{
    private ?SQLite3Stmt $writeStatement;
    private ?SQLite3Stmt $readStatement;

    public function __construct(private Connection $connection) {}

    public function readPosition(UUID $handlerId): ?EventId
    {
        if (!isset($this->readStatement)) {
            $this->readStatement = $this->connection->prepare(
                'SELECT eventId FROM positions WHERE handlerId=:handlerId',
            );
        }

        $this->readStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $row = $this->readStatement->execute()->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            return null;
        }

        if ($row['eventId'] === null) {
            return null;
        }

        return EventId::from($row['eventId']);
    }

    public function acquireLock(UUID $handlerId): void
    {
        $this->connection->exec('BEGIN IMMEDIATE');
    }

    public function writePosition(UUID $handlerId, EventId $eventId): void
    {
        $this->connection->exec('SAVEPOINT "' . $eventId->asString() . '"');

        if (!isset($this->writeStatement)) {
            $this->writeStatement = $this->connection->prepare(
                'INSERT OR REPLACE INTO positions(handlerId, eventId, timestamp) VALUES(:handlerId, :eventId, :timestamp)',
            );
        }

        $this->writeStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);
        $this->writeStatement->bindValue(':eventId', $eventId->asString(), SQLITE3_TEXT);
        $this->writeStatement->bindValue(':timestamp', Timestamp::generate()->asString(), SQLITE3_TEXT);

        $result = $this->writeStatement->execute();

        if ($result === false) {
            $this->connection->exec('ROLLBACK TO SAVEPOINT "' . $eventId->asString() . '"');

            throw new FailedToStoreStreamPositionException($handlerId, $eventId);
        }

        $this->connection->exec('RELEASE SAVEPOINT "' . $eventId->asString() . '"');
    }

    public function releaseLock(UUID $handlerId): void
    {
        $this->connection->exec('COMMIT');
    }

    public function resetPosition(UUID $handlerId): void
    {
        $statement = $this->connection->prepare(
            'UPDATE positions SET eventId=null,timestamp=:timestamp WHERE handlerId=:handlerId',
        );

        $statement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);
        $statement->bindValue(':timestamp', Timestamp::generate()->asString(), SQLITE3_TEXT);

        $result = $statement->execute();

        if ($result === false) {
            throw new FailedToResetStreamPositionException($handlerId);
        }
    }
}

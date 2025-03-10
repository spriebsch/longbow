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
use const SQLITE3_TEXT;

final readonly class SqliteStreamPositionWriter implements StreamPositionWriter
{
    public function __construct(private Connection $connection) {}

    public function writePosition(UUID $handlerId, EventId $eventId): void
    {
        // @todo cache statement

        $statement = $this->connection->prepare(
            'INSERT OR REPLACE INTO positions(handlerId, eventId, timestamp) VALUES(:handlerId, :eventId, :timestamp)'
        );

        $statement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);
        $statement->bindValue(':eventId', $eventId->asString(), SQLITE3_TEXT);
        $statement->bindValue(':timestamp', Timestamp::generate()->asString(), SQLITE3_TEXT);

        $result = $statement->execute();

        if ($result === false) {
            throw new FailedToStoreStreamPositionException($handlerId, $eventId);
        }
    }
}

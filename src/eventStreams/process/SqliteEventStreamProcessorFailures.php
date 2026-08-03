<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\eventStreams;

use spriebsch\DomainEvent\EventId;
use spriebsch\sqlite\Connection;
use spriebsch\timestamp\Timestamp;
use Throwable;
use const SQLITE3_TEXT;

final readonly class SqliteEventStreamProcessorFailures implements EventStreamProcessorFailures
{
    public function __construct(private Connection $connection) {}

    public function failureOf(EventStreamProcessorId $processorId): ?EventStreamProcessorFailure
    {
        $statement = $this->connection->prepare(
            'SELECT eventId, failedAt, exceptionClass, exceptionMessage
             FROM processorFailures
             WHERE processorId=:processorId',
        );
        $statement->bindValue(':processorId', $processorId->asString(), SQLITE3_TEXT);
        $result = $statement->execute();

        if ($result === false) {
            return null;
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            return null;
        }

        return new EventStreamProcessorFailure(
            $row['eventId'] === null ? null : EventId::from((string) $row['eventId']),
            (string) $row['failedAt'],
            (string) $row['exceptionClass'],
            (string) $row['exceptionMessage'],
        );
    }

    public function record(
        EventStreamProcessorId $processorId,
        ?EventId               $eventId,
        Throwable              $exception,
    ): void {
        $statement = $this->connection->prepare(
            'INSERT OR REPLACE INTO processorFailures(
                processorId, eventId, failedAt, exceptionClass, exceptionMessage
             ) VALUES(
                :processorId, :eventId, :failedAt, :exceptionClass, :exceptionMessage
             )',
        );
        $statement->bindValue(':processorId', $processorId->asString(), SQLITE3_TEXT);
        $statement->bindValue(':eventId', $eventId?->asString(), SQLITE3_TEXT);
        $statement->bindValue(':failedAt', Timestamp::generate()->asString(), SQLITE3_TEXT);
        $statement->bindValue(':exceptionClass', $exception::class, SQLITE3_TEXT);
        $statement->bindValue(':exceptionMessage', $exception->getMessage(), SQLITE3_TEXT);
        $statement->execute();
    }

    public function clear(EventStreamProcessorId $processorId): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM processorFailures WHERE processorId=:processorId',
        );
        $statement->bindValue(':processorId', $processorId->asString(), SQLITE3_TEXT);
        $statement->execute();
    }
}

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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\eventstore\EventId;
use spriebsch\longbow\FailedToStoreStreamPositionException;
use spriebsch\longbow\SqliteStreamPositionReader;
use spriebsch\longbow\SqliteStreamPositionSchema;
use spriebsch\longbow\SqliteStreamPositionWriter;
use spriebsch\sqlite\Connection;
use spriebsch\sqlite\SqliteConnection;
use spriebsch\uuid\UUID;
use SQLite3Stmt;

#[CoversClass(SqliteStreamPositionWriter::class)]
#[CoversClass(SqliteStreamPositionSchema::class)]
#[CoversClass(FailedToStoreStreamPositionException::class)]
#[UsesClass(SqliteStreamPositionReader::class)]
class SqliteStreamPositionWriterTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function writes_position_of_a_handler(): void
    {
        $connection = SqliteConnection::memory();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $writer = new SqliteStreamPositionWriter($connection);
        $reader = new SqliteStreamPositionReader($connection);

        $writer->writePosition($handlerId, $eventId);

        $this->assertSame(
            $eventId->asString(),
            $reader->readPosition($handlerId)->asString()
        );
    }

    #[Test]
    #[Group('feature')]
    public function updates_position_of_a_handler(): void
    {
        $connection = SqliteConnection::memory();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $writer = new SqliteStreamPositionWriter($connection);
        $reader = new SqliteStreamPositionReader($connection);

        $writer->writePosition($handlerId, EventId::generate());
        $writer->writePosition($handlerId, $eventId);

        $this->assertSame(
            $eventId->asString(),
            $reader->readPosition($handlerId)->asString()
        );
    }

    #[Test]
    #[Group('feature')]
    public function exception_when_fails(): void
    {
        $statement = $this->createMock(SQLite3Stmt::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('prepare')
            ->willReturn($statement);

        $writer = new SqliteStreamPositionWriter($connection);

        $this->expectException(FailedToStoreStreamPositionException::class);

        $writer->writePosition(UUID::generate(), EventId::generate());
    }
}

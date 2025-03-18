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
use PHPUnit\Framework\TestCase;
use spriebsch\eventstore\EventId;
use spriebsch\longbow\FailedToStoreStreamPositionException;
use spriebsch\longbow\SqliteStreamPosition;
use spriebsch\longbow\LongbowDatabaseSchema;
use spriebsch\sqlite\SqliteConnection;
use spriebsch\timestamp\Timestamp;
use spriebsch\uuid\UUID;
use SQLite3Exception;

#[CoversClass(SqliteStreamPosition::class)]
#[CoversClass(LongbowDatabaseSchema::class)]
#[CoversClass(FailedToStoreStreamPositionException::class)]
class SqliteStreamPositionTest extends TestCase
{
    private readonly string $db;

    protected function setUp(): void
    {
        $this->db = __DIR__ . '/../../../data/test.db';
    }

    protected function tearDown(): void
    {
        if (is_file($this->db)) {
            unlink($this->db);
        }
    }

    public function test_position_of_a_handler_is_initially_null(): void
    {
        $handlerId = UUID::generate();

        $connection = SqliteConnection::memory();
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $this->assertNull($position->readPosition($handlerId));
    }

    #[Group('feature')]
    public function test_reads_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = UUID::generate();

        $connection = SqliteConnection::memory();
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $writeStatement = $connection->prepare(
            'INSERT OR REPLACE INTO positions(handlerId, eventId, timestamp) VALUES(:handlerId, :eventId, :timestamp)',
        );

        $writeStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);
        $writeStatement->bindValue(':eventId', $eventId->asString(), SQLITE3_TEXT);
        $writeStatement->bindValue(':timestamp', Timestamp::generate()->asString(), SQLITE3_TEXT);

        $writeStatement->execute();

        $position = new SqliteStreamPosition($connection);

        $this->assertEquals(
            $eventId->asString(),
            $position->readPosition($handlerId)->asString(),
        );
    }

    #[Group('feature')]
    public function test_writes_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $connection = SqliteConnection::memory();
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $position->acquireLock($handlerId);
        $position->readPosition($handlerId);
        $position->writePosition($handlerId, $eventId);
        $position->releaseLock($handlerId);

        $this->assertSame(
            $eventId->asString(),
            $position->readPosition($handlerId)->asString(),
        );
    }

    #[Group('feature')]
    public function test_updates_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $connection = SqliteConnection::memory();
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $position->acquireLock($handlerId);
        $position->readPosition($handlerId);
        $position->writePosition($handlerId, EventId::generate());
        $position->releaseLock($handlerId);

        $position->acquireLock($handlerId);
        $position->readPosition($handlerId);
        $position->writePosition($handlerId, $eventId);
        $position->releaseLock($handlerId);

        $this->assertSame(
            $eventId->asString(),
            $position->readPosition($handlerId)->asString(),
        );
    }

    #[Group('feature')]
    public function test_transaction_allows_read(): void
    {
        $handlerId = UUID::generate();
        $firstEventId = EventId::generate();
        $secondEventId = EventId::generate();

        $connection = SqliteConnection::from($this->db);
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $secondConnection = SqliteConnection::from($this->db);

        $position = new SqliteStreamPosition($connection);
        $secondPosition = new SqliteStreamPosition($secondConnection);

        $position->acquireLock($handlerId);
        $position->readPosition($handlerId);
        $position->writePosition($handlerId, $firstEventId);
        $position->releaseLock($handlerId);

        $position->acquireLock($handlerId);
        $position->readPosition($handlerId);
        $before = $secondPosition->readPosition($handlerId);
        $position->writePosition($handlerId, $secondEventId);
        $position->releaseLock($handlerId);

        $this->assertSame(
            $firstEventId->asString(),
            $before->asString(),
        );
    }

    public function test_transaction_blocks_write(): void
    {
        $handlerId = UUID::generate();

        $connection = SqliteConnection::from($this->db);
        $secondConnection = SqliteConnection::from($this->db);

        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);
        $secondPosition = new SqliteStreamPosition($secondConnection);

        $position->acquireLock($handlerId);

        $this->expectException(SQLite3Exception::class);

        $secondPosition->writePosition($handlerId, EventId::generate());
    }
}

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
use spriebsch\longbow\SqliteStreamPositionSchema;
use spriebsch\sqlite\SqliteConnection;
use spriebsch\timestamp\Timestamp;
use spriebsch\uuid\UUID;
use SQLite3;
use SQLite3Exception;
use const SQLITE3_ASSOC;

#[CoversClass(SqliteStreamPosition::class)]
#[CoversClass(SqliteStreamPositionSchema::class)]
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
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $this->assertNull($position->readPositionAndLock($handlerId));
    }

    #[Group('feature')]
    public function test_reads_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = UUID::generate();

        $connection = SqliteConnection::memory();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

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
            $position->readPositionAndLock($handlerId)->asString(),
        );
    }

    #[Group('feature')]
    public function test_writes_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $connection = SqliteConnection::memory();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $position->readPositionAndLock($handlerId);
        $position->writePositionAndReleaseLock($handlerId, $eventId);

        $readStatement = $connection->prepare(
            'SELECT eventId FROM positions WHERE handlerId=:handlerId',
        );

        $readStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $result = $readStatement->execute();
        $this->assertSame(
            $eventId->asString(),
            $result->fetchArray(SQLITE3_ASSOC)['eventId']
        );
    }

    #[Group('feature')]
    public function test_updates_position_of_a_handler(): void
    {
        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $connection = SqliteConnection::memory();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $position = new SqliteStreamPosition($connection);

        $position->readPositionAndLock($handlerId);
        $position->writePositionAndReleaseLock($handlerId, EventId::generate());

        $position->readPositionAndLock($handlerId);
        $position->writePositionAndReleaseLock($handlerId, $eventId);

        $readStatement = $connection->prepare(
            'SELECT eventId FROM positions WHERE handlerId=:handlerId',
        );

        $readStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $result = $readStatement->execute();
        $this->assertSame(
            $eventId->asString(),
            $result->fetchArray(SQLITE3_ASSOC)['eventId']
        );
    }

    #[Group('feature')]
    public function test_transaction_allows_read(): void
    {
        $handlerId = UUID::generate();
        $eventId = EventId::generate();

        $connection = SqliteConnection::from($this->db);
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $secondConnection = SqliteConnection::from($this->db);

        $readStatement = $secondConnection->prepare(
            'SELECT eventId FROM positions WHERE handlerId=:handlerId',
        );

        $readStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $position = new SqliteStreamPosition($connection);

        $position->readPositionAndLock($handlerId);
        $result = $readStatement->execute()->fetchArray();
        $position->writePositionAndReleaseLock($handlerId, $eventId);

        $this->assertFalse($result);
    }

    public function test_transaction_blocks_write(): void
    {
        $handlerId = UUID::generate();

        $connection = SqliteConnection::from($this->db);
        $secondConnection = SqliteConnection::from($this->db);

        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        $writeStatement = $secondConnection->prepare(
            'INSERT OR REPLACE INTO positions(handlerId, eventId, timestamp) VALUES(:handlerId, :eventId, :timestamp)',
        );

        $writeStatement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);
        $writeStatement->bindValue(':eventId', EventId::generate()->asString(), SQLITE3_TEXT);
        $writeStatement->bindValue(':timestamp', Timestamp::generate()->asString(), SQLITE3_TEXT);

        $position = new SqliteStreamPosition($connection);

        $position->readPositionAndLock($handlerId);

        $this->expectException(SQLite3Exception::class);

        $writeStatement->execute();
    }
}

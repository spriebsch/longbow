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
use spriebsch\longbow\SqliteStreamPositionReader;
use spriebsch\sqlite\Connection;
use spriebsch\uuid\UUID;
use SQLite3Result;
use SQLite3Stmt;

#[CoversClass(SqliteStreamPositionReader::class)]
class SqliteStreamPositionReaderTest extends TestCase
{
    #[Group('feature')]
    public function test_reads_position(): void
    {
        $handlerId = UUID::generate();
        $eventId = UUID::generate();

        $result = $this->createMock(SQLite3Result::class);
        $result
            ->expects($this->once())
            ->method('fetchArray')
            ->with(SQLITE3_ASSOC)
            ->willReturn(['eventId' => $eventId->asString()]);

        $statement = $this->createMock(SQLite3Stmt::class);
        $statement
            ->expects($this->once())
            ->method('bindValue')
            ->with(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT eventId FROM positions WHERE handlerId=:handlerId')
            ->willReturn($statement);

        $writer = new SqliteStreamPositionReader($connection);

        $this->assertEquals($eventId->asString(), $writer->readPosition($handlerId)->asString());
    }

    #[Group('feature')]
    public function test_null_when_no_position(): void
    {
        $handlerId = UUID::generate();

        $result = $this->createStub(SQLite3Result::class);
        $result
            ->method('fetchArray')
            ->willReturn(false);

        $statement = $this->createStub(SQLite3Stmt::class);
        $statement
            ->method('execute')
            ->willReturn($result);

        $connection = $this->createStub(Connection::class);
        $connection
            ->method('prepare')
            ->willReturn($statement);

        $writer = new SqliteStreamPositionReader($connection);

        $this->assertNull($writer->readPosition($handlerId));
    }
}

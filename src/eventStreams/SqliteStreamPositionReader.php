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
use spriebsch\uuid\UUID;
use const SQLITE3_TEXT;

final readonly class SqliteStreamPositionReader implements StreamPositionReader
{
    public function __construct(private Connection $connection) {}

    public function readPosition(UUID $handlerId): ?EventId
    {
        // @todo cache statement

        $statement = $this->connection->prepare('SELECT eventId FROM positions WHERE handlerId=:handlerId');

        $statement->bindValue(':handlerId', $handlerId->asString(), SQLITE3_TEXT);

        $row = $statement->execute()->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            return null;
        }

        return EventId::from($row['eventId']);
    }
}

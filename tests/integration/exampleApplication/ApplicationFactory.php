<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\example;

use spriebsch\diContainer\AbstractFactory;
use spriebsch\eventstore\EventReader;
use spriebsch\eventstore\EventWriter;
use spriebsch\eventstore\SqliteEventReader;
use spriebsch\eventstore\SqliteEventStoreSchema;
use spriebsch\eventstore\SqliteEventWriter;
use spriebsch\longbow\SqliteStreamPositionSchema;
use spriebsch\sqlite\SqliteConnection;

final readonly class ApplicationFactory extends AbstractFactory
{
    public function eventWriter(): EventWriter
    {
        return SqliteEventWriter::from($this->container->get('longbowEventStore'));
    }

    public function eventReader(): EventReader
    {
        return SqliteEventReader::from($this->container->get('longbowEventStore'));
    }

    public function longbowEventStore(): SqliteConnection
    {
        return $this->connection();
    }

    public function longbowPositionStore(): SqliteConnection
    {
        return $this->connection();
    }

    private function connection(): SqliteConnection
    {
        $connection = SqliteConnection::memory();
        SqliteEventStoreSchema::from($connection)->createIfNotExists();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        return $connection;
    }
}

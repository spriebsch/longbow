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

use spriebsch\eventstore\EventReader;
use spriebsch\eventstore\EventStream;
use spriebsch\eventstore\EventWriter;
use spriebsch\eventstore\SqliteEventReader;
use spriebsch\eventstore\SqliteEventStoreSchema;
use spriebsch\eventstore\SqliteEventWriter;
use spriebsch\longbow\SqliteStreamPositionSchema;
use spriebsch\sqlite\SqliteConnection;

class ApplicationFactory
{
    private ?Something        $something   = null;
    private ?Something2       $something2  = null;
    private ?SqliteConnection $connection  = null;
    private EventReader       $eventReader;
    private EventWriter       $eventWriter;

    public function eventWriter(): EventWriter
    {
        return SqliteEventWriter::from($this->getConnection());
    }

    public function streamPositionConnection(): SqliteConnection
    {
        return $this->getConnection();
    }

    public function getConnection(): SqliteConnection
    {
        if ($this->connection === null) {
            $this->connection = $this->connection();
        }

        return $this->connection;
    }

    public function getEventReader(): EventReader
    {
        if (!isset($this->eventReader)) {
            $this->eventReader = $this->eventReader();
        }

        return $this->eventReader;
    }

    public function getEventWriter(): EventWriter
    {
        if (!isset($this->eventWriter)) {
            $this->eventWriter = $this->eventWriter();
        }

        return $this->eventWriter;
    }

    public function someEventStream(): EventStream
    {
        return new SomeEventStream($this->getEventReader());
    }

    public function someEventStreamProcessor(): SomeEventStreamProcessor
    {
        return new SomeEventStreamProcessor($this->getSomething2());
    }

    public function someCommandHandler(): SomeCommandHandler
    {
        return new SomeCommandHandler($this->getEventWriter());
    }

    public function someEventHandler(): SomeEventHandler
    {
        return new SomeEventHandler($this->getSomething());
    }

    public function getSomething(): Something
    {
        if ($this->something === null) {
            $this->something = $this->something();
        }

        return $this->something;
    }

    private function something(): Something
    {
        return new Something;
    }

    public function getSomething2(): Something2
    {
        if ($this->something2 === null) {
            $this->something2 = $this->something2();
        }

        return $this->something2;
    }

    private function something2(): Something2
    {
        return new Something2;
    }

    private function connection(): SqliteConnection
    {
        $connection = SqliteConnection::memory();
        SqliteEventStoreSchema::from($connection)->createIfNotExists();
        SqliteStreamPositionSchema::from($connection)->createIfNotExists();

        return $connection;
    }

    private function eventReader(): EventReader
    {
        return SqliteEventReader::from($this->getConnection());
    }
}
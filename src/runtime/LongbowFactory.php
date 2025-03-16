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

use spriebsch\diContainer\Container;
use spriebsch\eventstore\EventFactory;
use spriebsch\eventstore\EventReader;
use spriebsch\eventstore\EventWriter;
use spriebsch\eventstore\SqliteEventReader;
use spriebsch\eventstore\SqliteEventStoreSchema;
use spriebsch\eventstore\SqliteEventWriter;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\commands\CommandHandlerMap;
use spriebsch\longbow\commands\LongbowCommandDispatcher;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\EventDispatcher;
use spriebsch\longbow\events\EventHandlerMap;
use spriebsch\longbow\events\LongbowEventDispatcher;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\EventStreamProcessorMap;
use spriebsch\longbow\eventStreams\LongbowEventStreamDispatcher;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\sqlite\SqliteConnection;

final readonly class LongbowFactory
{
    private ?SqliteConnection $positionsConnection;
    private ?SqliteConnection $eventStoreConnection;

    public function __construct(
        private Directory $orchestrationDirectory,
        private File      $eventMap,
        private string    $eventStoreDb,
        private string    $positionsDb,
        private Container $container,
    )
    {
        EventFactory::configureWith($this->eventMap->require());
    }

    public function commandDispatcher(): CommandDispatcher
    {
        return new LongbowCommandDispatcher(
            $this->commandHandlerMap(),
            $this->container,
            $this->eventDispatcher(),
        );
    }


    public function eventReader(): EventReader
    {
        return SqliteEventReader::from(
            $this->eventStoreConnection(),
        );
    }

    public function eventWriter(): EventWriter
    {
        return SqliteEventWriter::from(
            $this->eventStoreConnection(),
        );
    }

    public function eventDispatcher(): EventDispatcher
    {
        return new LongbowEventDispatcher($this->eventHandlerMap(), $this->container);
    }

    public function eventStreamDispatcher(): EventStreamDispatcher
    {
        return new LongbowEventStreamDispatcher(
            $this->eventStreamProcessorMap(),
            $this->streamPositionReader(),
            $this->streamPositionWriter(),
            $this->container,
        );
    }

    private function commandHandlerMap(): CommandHandlerMap
    {
        return CommandHandlerMap::fromFile(
            $this->orchestrationDirectory()
                ->file(OrchestrateCommandHandlers::SERIALIZATION_FILE),
        );
    }

    private function eventHandlerMap(): EventHandlerMap
    {
        return EventHandlerMap::fromFile(
            $this->orchestrationDirectory()
                ->file(OrchestrateEventHandlers::SERIALIZATION_FILE),
        );
    }

    private function streamPositionReader(): StreamPositionReader
    {
        return new SqliteStreamPositionHandler($this->streamPositionConnection());
    }

    private function streamPositionWriter(): StreamPositionWriter
    {
        return new SqliteStreamPositionWriter($this->streamPositionConnection());
    }

    private function eventStreamProcessorMap(): EventStreamProcessorMap
    {
        return EventStreamProcessorMap::fromFile(
            $this->orchestrationDirectory()
                ->file(OrchestrateEventStreamProcessors::SERIALIZATION_FILE),
        );
    }

    private function orchestrationDirectory(): Directory
    {
        return $this->orchestrationDirectory;
    }

    private function streamPositionConnection(): SqliteConnection
    {
        if (!isset($this->positionsConnection)) {
            $this->positionsConnection = $this->sqliteConnection($this->eventStoreDb);

            SqliteStreamPositionSchema::from($this->positionsConnection)->createIfNotExists();
        }

        return $this->positionsConnection;
    }

    private function eventStoreConnection(): SqliteConnection
    {
        if (!isset($this->eventStoreConnection)) {
            $this->eventStoreConnection = $this->sqliteConnection($this->positionsDb);

            SqliteEventStoreSchema::from($this->eventStoreConnection)->createIfNotExists();
        }

        return $this->eventStoreConnection;
    }

    private function sqliteConnection(string $database): SqliteConnection
    {
        return SqliteConnection::from($database);
    }
}

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

use spriebsch\diContainer\AbstractFactory;
use spriebsch\eventstore\EventReader;
use spriebsch\eventstore\EventWriter;
use spriebsch\eventstore\SqliteEventReader;
use spriebsch\eventstore\SqliteEventStoreSchema;
use spriebsch\eventstore\SqliteEventWriter;
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

final readonly class LongbowFactory extends AbstractFactory
{
    public function CommandDispatcher(): CommandDispatcher
    {
        return $this->container->get(LongbowCommandDispatcher::class);
    }

    public function CommandHandlerMap(): CommandHandlerMap
    {
        return CommandHandlerMap::fromFile(
            $this->configuration()->orchestrationDirectory()
                ->file(OrchestrateCommandHandlers::SERIALIZATION_FILE),
        );
    }

    public function EventHandlerMap(): EventHandlerMap
    {
        return EventHandlerMap::fromFile(
            $this->configuration()->orchestrationDirectory()
                ->file(OrchestrateEventHandlers::SERIALIZATION_FILE),
        );
    }

    public function EventStreamProcessorMap(): EventStreamProcessorMap
    {
        return EventStreamProcessorMap::fromFile(
            $this->configuration()->orchestrationDirectory()
                ->file(OrchestrateEventStreamProcessors::SERIALIZATION_FILE),
        );
    }

    public function EventReader(): EventReader
    {
        return SqliteEventReader::from(
            $this->container->get('eventStoreConnection'),
        );
    }

    public function EventWriter(): EventWriter
    {
        return SqliteEventWriter::from(
            $this->container->get('eventStoreConnection'),
        );
    }

    public function EventDispatcher(): EventDispatcher
    {
        return $this->container->get(LongbowEventDispatcher::class);
    }

    public function EventStreamDispatcher(): EventStreamDispatcher
    {
        return new LongbowEventStreamDispatcher(
            $this->EventStreamProcessorMap(),
            $this->StreamPosition(),
            $this->container,
        );
    }

    public function StreamPosition(): StreamPosition
    {
        return new SqliteStreamPosition($this->container->get('longbowDatabaseConnection'));
    }

    public function longbowDatabaseConnection(): SqliteConnection
    {
        $connection = SqliteConnection::from($this->configuration()->longbowDatabase());
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        return $connection;
    }

    public function eventStoreConnection(): SqliteConnection
    {
        $connection = SqliteConnection::from($this->configuration()->eventStore());
        SqliteEventStoreSchema::from($connection)->createIfNotExists();

        return $connection;
    }
}

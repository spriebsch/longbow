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

use spriebsch\eventstore\EventFactory;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\commands\CommandHandlerMap;
use spriebsch\longbow\commands\LongbowCommandDispatcher;
use spriebsch\longbow\commands\LongbowCommandHandlerFactory;
use spriebsch\longbow\commands\LongbowCommandHandlerMap;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\EventDispatcher;
use spriebsch\longbow\events\EventHandlerMap;
use spriebsch\longbow\events\LongbowEventDispatcher;
use spriebsch\longbow\events\LongbowEventHandlerFactory;
use spriebsch\longbow\events\LongbowEventHandlerMap;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\LongbowEventStreamDispatcher;
use spriebsch\longbow\eventStreams\LongbowEventStreamFactory;
use spriebsch\longbow\eventStreams\LongbowEventStreamProcessorFactory;
use spriebsch\longbow\eventStreams\LongbowEventStreamProcessorMap;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\sqlite\Connection;

final class LongbowFactory
{
    public function __construct(
        private readonly Directory $orchestrationDirectory,
        private readonly File      $eventMap,
        private readonly object    $applicationFactory
    ) {
        EventFactory::configureWith($this->eventMap->require());
    }

    public function commandDispatcher(): CommandDispatcher
    {
        return new LongbowCommandDispatcher(
            $this->commandHandlerMap(),
            new LongbowCommandHandlerFactory($this->applicationFactory()),
            $this->eventDispatcher()
        );
    }

    public function eventStreamDispatcher(): EventStreamDispatcher
    {
        return new LongbowEventStreamDispatcher(
            $this->eventStreamProcessorMap(),
            $this->exclusiveLock(__DIR__ . '/lockfile-@todo'),
            $this->streamPositionReader(),
            $this->streamPositionWriter(),
            new LongbowEventStreamProcessorFactory($this->applicationFactory()),
            new LongbowEventStreamFactory($this->applicationFactory())
        );
    }

    private function commandHandlerMap(): CommandHandlerMap
    {
        return new LongbowCommandHandlerMap(
            $this->orchestrationDirectory()
                 ->file(OrchestrateCommandHandlers::SERIALIZATION_FILE)
        );
    }
    
    private function eventDispatcher(): EventDispatcher
    {
        return new LongbowEventDispatcher(
            $this->eventHandlerMap(),
            new LongbowEventHandlerFactory($this->applicationFactory())
        );
    }

    private function eventHandlerMap(): EventHandlerMap
    {
        return new LongbowEventHandlerMap(
            $this->orchestrationDirectory()
                 ->file(OrchestrateEventHandlers::SERIALIZATION_FILE)
        );
    }

    private function streamPositionReader(): StreamPositionReader
    {
        return new SqliteStreamPositionReader($this->streamPositionConnection());
    }

    private function streamPositionWriter(): StreamPositionWriter
    {
        return new SqliteStreamPositionWriter($this->streamPositionConnection());
    }

    private function exclusiveLock(string $lockFile): ExclusiveLock
    {
        return new LongbowExclusiveLock($lockFile);
    }

    private function streamPositionConnection(): Connection
    {
        return $this->applicationFactory()->streamPositionConnection();
    }

    private function eventStreamProcessorMap(): LongbowEventStreamProcessorMap
    {
        return new LongbowEventStreamProcessorMap(
            $this->orchestrationDirectory()
                 ->file(OrchestrateEventStreamProcessors::SERIALIZATION_FILE)
        );
    }

    private function orchestrationDirectory(): Directory
    {
        return $this->orchestrationDirectory;
    }

    private function applicationFactory(): SafeFactory
    {
        return new SafeFactory($this->applicationFactory);
    }
}

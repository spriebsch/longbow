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

use InvalidArgumentException;
use spriebsch\diContainer\AbstractFactory;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\commands\CommandHandlerMap;
use spriebsch\longbow\commands\LongbowCommandDispatcher;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\EventDispatcher;
use spriebsch\longbow\events\EventHandlerMap;
use spriebsch\longbow\events\LongbowEventDispatcher;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\EventStreamProcessorFailures;
use spriebsch\longbow\eventStreams\EventStreamProcessorMap;
use spriebsch\longbow\eventStreams\LongbowEventStreamDispatcher;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\longbow\eventStreams\SqliteEventStreamProcessorFailures;
use spriebsch\sqlite\SqliteConnection;
use spriebsch\sequora\EventReader;
use spriebsch\sequora\EventWriter;
use spriebsch\sequora\SequoraReader;
use spriebsch\sequora\SequoraWriter;
use spriebsch\sequora\SqliteDatabaseReader;
use spriebsch\sequora\SqliteDatabaseWriter;
use spriebsch\sequora\SqliteSequoraSchema;

final readonly class LongbowFactory extends AbstractFactory
{
    public function CommandDispatcher(): CommandDispatcher
    {
        $dispatcher = $this->container->get(LongbowCommandDispatcher::class);
        assert($dispatcher instanceof CommandDispatcher);

        return $dispatcher;
    }

    public function CommandHandlerMap(): CommandHandlerMap
    {
        return CommandHandlerMap::fromFile(
            $this->longbowConfiguration()->orchestrationDirectory()
                ->file(OrchestrateCommandHandlers::SERIALIZATION_FILE),
        );
    }

    public function EventHandlerMap(): EventHandlerMap
    {
        return EventHandlerMap::fromFile(
            $this->longbowConfiguration()->orchestrationDirectory()
                ->file(OrchestrateEventHandlers::SERIALIZATION_FILE),
        );
    }

    public function EventStreamProcessorMap(): EventStreamProcessorMap
    {
        return EventStreamProcessorMap::fromFile(
            $this->longbowConfiguration()->orchestrationDirectory()
                ->file(OrchestrateEventStreamProcessors::SERIALIZATION_FILE),
        );
    }

    public function EventReader(): EventReader
    {
        return SequoraReader::from(
            SqliteDatabaseReader::from(
                $this->sequoraConnection(),
                $this->topicMap(),
            ),
        );
    }

    public function EventWriter(): EventWriter
    {
        return SequoraWriter::from(
            SqliteDatabaseWriter::from($this->sequoraConnection()),
        );
    }

    public function EventDispatcher(): EventDispatcher
    {
        $dispatcher = $this->container->get(LongbowEventDispatcher::class);
        assert($dispatcher instanceof EventDispatcher);

        return $dispatcher;
    }

    public function EventStreamDispatcher(): EventStreamDispatcher
    {
        return new LongbowEventStreamDispatcher(
            $this->EventStreamProcessorMap(),
            $this->StreamPosition(),
            $this->EventStreamProcessorFailures(),
            $this->container,
        );
    }

    public function EventStreamProcessorFailures(): EventStreamProcessorFailures
    {
        $connection = $this->container->get(SqliteConnection::class, 'longbow');
        assert($connection instanceof SqliteConnection);

        return new SqliteEventStreamProcessorFailures($connection);
    }

    public function StreamPosition(): StreamPosition
    {
        $connection = $this->container->get(SqliteConnection::class, 'longbow');
        assert($connection instanceof SqliteConnection);

        return new SqliteStreamPosition($connection);
    }

    public function SqliteConnection(string $database): SqliteConnection
    {
        $connection = match ($database) {
            'longbow' => $this->createLongbowDatabaseConnection(),
            'sequora' => $this->createSequoraDatabaseConnection(),
            default   => throw new InvalidArgumentException(
                sprintf('Unknown SQLite database %s', $database),
            ),
        };

        if (!$connection->isInMemoryDatabase() && is_file($connection->database()) && fileowner($connection->database()) === posix_getuid()) {
            chmod($connection->database(), 0666);
        }

        return $connection;
    }

    private function createLongbowDatabaseConnection(): SqliteConnection
    {
        $connection = SqliteConnection::from($this->longbowConfiguration()->longbowDatabase());
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        return $connection;
    }

    private function createSequoraDatabaseConnection(): SqliteConnection
    {
        $connection = SqliteConnection::from($this->longbowConfiguration()->sequoraDatabase());
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        return $connection;
    }

    private function longbowConfiguration(): LongbowConfiguration
    {
        assert($this->configuration instanceof LongbowConfiguration);

        return $this->configuration;
    }

    private function sequoraConnection(): SqliteConnection
    {
        $connection = $this->container->get(SqliteConnection::class, 'sequora');
        assert($connection instanceof SqliteConnection);

        return $connection;
    }

    /** @return array<string, string> */
    private function topicMap(): array
    {
        $topicMap = $this->longbowConfiguration()->topicMap()->require();
        assert(is_array($topicMap));

        /** @var array<string, string> $topicMap */
        return $topicMap;
    }
}

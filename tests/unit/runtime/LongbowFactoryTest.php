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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use spriebsch\eventstore\EventWriter;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\sqlite\Connection;

#[CoversClass(LongbowFactory::class)]
#[Medium]
class LongbowFactoryTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function creates_CommandDispatcher(): void
    {
        $directory = new FakeDirectory('/fake');
        $directory->createFile(
            OrchestrateCommandHandlers::SERIALIZATION_FILE, 'the-content'
        );
        $directory->createFile(
            OrchestrateEventHandlers::SERIALIZATION_FILE, 'the-content'
        );

        $eventWriter = $this->createStub(EventWriter::class);

        $factory = new LongbowFactory(
            $directory,
            Filesystem::from(__DIR__ . '/../../fixtures/events.php'),
            new readonly class($eventWriter) {

                public function __construct(private EventWriter $eventWriter) {}

                public function eventWriter(): EventWriter
                {
                    return $this->eventWriter;
                }
            }
        );

        $this->assertInstanceOf(
            CommandDispatcher::class,
            $factory->commandDispatcher()
        );
    }

    #[Test]
    #[Group('feature')]
    public function creates_EventStreamDispatcher(): void
    {
        $directory = new FakeDirectory('/fake');
        $directory->createFile(
            OrchestrateEventStreamProcessors::SERIALIZATION_FILE, 'the-content'
        );

        $connection = $this->createStub(Connection::class);

        $factory = new LongbowFactory(
            $directory,
            Filesystem::from(__DIR__ . '/../../fixtures/events.php'),
            new readonly class($connection) {

                public function __construct(private Connection $connection) {}

                public function streamPositionConnection(): Connection
                {
                    return $this->connection;
                }
            }
        );

        $this->assertInstanceOf(
            EventStreamDispatcher::class,
            $factory->eventStreamDispatcher()
        );
    }
}

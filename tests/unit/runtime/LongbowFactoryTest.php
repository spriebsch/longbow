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
use spriebsch\diContainer\DIContainer;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\longbow\example\ApplicationConfiguration;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\sqlite\Connection;

#[CoversClass(LongbowFactory::class)]
#[Medium]
class LongbowFactoryTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function creates_CommandDispatcher(): void
    {
        $container = new DiContainer(new ApplicationConfiguration, ApplicationFactory::class);

        $directory = new FakeDirectory('/fake');
        $directory->createFile(
            OrchestrateCommandHandlers::SERIALIZATION_FILE, '<?php declare(strict_types=1); return [];'
        );
        $directory->createFile(
            OrchestrateEventHandlers::SERIALIZATION_FILE, '<?php declare(strict_types=1); return [];'
        );

        $factory = new LongbowFactory(
            $directory,
            Filesystem::from(__DIR__ . '/../../fixtures/events.php'),
            ':memory:',
            ':memory:',
            $container,
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
        $container = new DiContainer(new ApplicationConfiguration, ApplicationFactory::class);

        $directory = new FakeDirectory('/fake');
        $directory->createFile(
            OrchestrateEventStreamProcessors::SERIALIZATION_FILE, '<?php declare(strict_types=1); return [];'
        );

        $connection = $this->createStub(Connection::class);

        $factory = new LongbowFactory(
            $directory,
            Filesystem::from(__DIR__ . '/../../fixtures/events.php'),
            ':memory:',
            ':memory:',
            $container,
        );

        $this->assertInstanceOf(
            EventStreamDispatcher::class,
            $factory->eventStreamDispatcher()
        );
    }
}

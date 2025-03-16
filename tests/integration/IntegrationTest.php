<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use spriebsch\diContainer\Container;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\example\ApplicationConfiguration;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\longbow\example\SomeCommand;
use spriebsch\longbow\example\SomeCommandHandler;
use spriebsch\longbow\example\SomeEvent;
use spriebsch\longbow\example\SomeEventHandler;
use spriebsch\longbow\example\SomeEventStream;
use spriebsch\longbow\example\SomeEventStreamProcessor;
use spriebsch\longbow\example\Something;
use spriebsch\longbow\example\Something2;
use spriebsch\longbow\Longbow;
use spriebsch\longbow\orchestration\LongbowOrchestration;
use spriebsch\uuid\UUID;

#[CoversNothing]
class IntegrationTest extends TestCase
{
    #[Test]
    public function exampleApplication(): void
    {
        $orchestrationDirectory = $this->prepare();
        $this->orchestrate($orchestrationDirectory);
        $this->runLongbow($orchestrationDirectory);
    }

    private function prepare(): Directory
    {
        $orchestrationDirectory = Filesystem::from(__DIR__ . '/orchestration');

        $orchestrationDirectory->deleteFile('_LongbowCommandHandlers.php');
        $orchestrationDirectory->deleteFile('_LongbowEventHandlers.php');
        $orchestrationDirectory->deleteFile('_LongbowEventStreamProcessors.php');

        return $orchestrationDirectory;
    }

    private function orchestrate(Directory $directory): void
    {
        $orchestration = LongbowOrchestration::initialize();
        $orchestration
            ->command(SomeCommand::class)
            ->isHandledBy(SomeCommandHandler::class);

        $orchestration
            ->onEvent(SomeEvent::class)
            ->runSynchronously(SomeEventHandler::class);

        $orchestration
            ->eventStream(SomeEventStream::class)
            ->isProcessedBy(SomeEventStreamProcessor::class);

        $orchestration->exportOrchestrationTo($directory);
    }

    private function runLongbow(Directory $orchestrationDirectory): void
    {
        $eventMap = Filesystem::from(__DIR__ . '/../fixtures/events.php');

        $configuration = new ApplicationConfiguration;
        $container = new Container($configuration, ApplicationFactory::class);

        // $applicationFactory = new ApplicationFactory;
        $payload = UUID::generate()->asString();

        Longbow::configure(
            $orchestrationDirectory,
            $eventMap,
            ':memory:',
            ':memory:',
            $container
        );

        $event = Longbow::handleCommand(new SomeCommand($payload));
        $something = $container->get(Something::class);

        Longbow::processEvents();
        $something2 = $container->get(Something2::class);

        $this->assertSame($payload, $event->payload());
        $this->assertSame($something->payload(), $payload);
        $this->assertSame($something2->payload(), $payload);
    }
}

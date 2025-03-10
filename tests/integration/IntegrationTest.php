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
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\longbow\example\SomeCommand;
use spriebsch\longbow\example\SomeCommandHandler;
use spriebsch\longbow\example\SomeEvent;
use spriebsch\longbow\example\SomeEventHandler;
use spriebsch\longbow\example\SomeEventStream;
use spriebsch\longbow\example\SomeEventStreamProcessor;
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

        $applicationFactory = new ApplicationFactory;
        $payload = UUID::generate()->asString();

        Longbow::configure(
            $orchestrationDirectory,
            $eventMap,
            $applicationFactory
        );

        $event = Longbow::handleCommand(new SomeCommand($payload));
        $something = $applicationFactory->getSomething();

        Longbow::processEvents();
        $something2 = $applicationFactory->getSomething2();

        $this->assertSame($payload, $event->payload());
        $this->assertSame($something->payload(), $payload);
        $this->assertSame($something2->payload(), $payload);
    }
}

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
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\longbow\example\LongbowConfiguration;
use spriebsch\longbow\example\SomeCommand;
use spriebsch\longbow\example\SomeCommandHandler;
use spriebsch\longbow\example\SomeEvent;
use spriebsch\longbow\example\SomeEventHandler;
use spriebsch\longbow\example\SomeEventStream;
use spriebsch\longbow\example\SomeEventStreamProcessor;
use spriebsch\longbow\example\EventHandlerSideEffect;
use spriebsch\longbow\example\EventStreamProcessorSideEffect;
use spriebsch\longbow\Longbow;
use spriebsch\longbow\orchestration\LongbowOrchestration;
use spriebsch\uuid\UUID;

#[CoversNothing]
class IntegrationTest extends TestCase
{
    public function test_exampleApplication(): void
    {
        $orchestrationDirectory = $this->prepare();
        $this->orchestrate($orchestrationDirectory);
        $this->runLongbow($orchestrationDirectory);
    }

    private function prepare(): Directory
    {
        $orchestrationDirectory = Filesystem::from(__DIR__ . '/../../data');

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
        $payload = UUID::generate()->asString();

        $eventMap = Filesystem::from(__DIR__ . '/../fixtures/events.php');

        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        Longbow::configure(
            $configuration,
            $eventMap,
            ApplicationFactory::class,
        );

        $event = Longbow::handleCommand(new SomeCommand($payload));
        $eventHandlerSideEffect = Longbow::container()->get(EventHandlerSideEffect::class);

        Longbow::processEvents();
        $streamProcessorSideEffect = Longbow::container()->get(EventStreamProcessorSideEffect::class);
        Longbow::reset();

        $this->assertSame($payload, $event->payload());
        $this->assertSame($eventHandlerSideEffect->payload(), $payload);
        $this->assertSame($streamProcessorSideEffect->payload(), $payload);
    }
}

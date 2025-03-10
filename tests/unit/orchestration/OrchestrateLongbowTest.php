<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\orchestration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\Directory;
use spriebsch\longbow\commands\LongbowOrchestrateCommandHandlers;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;
use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventStream;

#[CoversClass(LongbowOrchestration::class)]
#[CoversClass(LongbowOrchestrateCommandHandlers::class)]
class OrchestrateLongbowTest extends TestCase
{
    public function test_orchestrate_command_handler(): void
    {
        $orchestrateCommandHandlers = $this->orchestrateCommandHandlers();

        $orchestrateLongbow = new LongbowOrchestration(
            $orchestrateCommandHandlers,
            $this->orchestrateEventHandlers(),
            $this->orchestrateEventStreamProcessors()
        );

        $commandClass = TestCommand::class;

        $orchestrateLongbow->command($commandClass);

        $this->assertSame($commandClass, $orchestrateCommandHandlers->commandClass);
    }

    public function test_orchestrate_event_handler(): void
    {
        $orchestrateEventHandlers = $this->orchestrateEventHandlers();

        $orchestrateLongbow = new LongbowOrchestration(
            $this->orchestrateCommandHandlers(),
            $orchestrateEventHandlers,
            $this->orchestrateEventStreamProcessors()
        );

        $eventClass = TestEvent::class;

        $orchestrateLongbow->onEvent($eventClass);

        $this->assertSame($eventClass, $orchestrateEventHandlers->eventClass);
    }

    public function test_orchestrate_event_stream_processors(): void
    {
        $orchestrateEventStreamProcessors = $this->orchestrateEventStreamProcessors();

        $orchestrateLongbow = new LongbowOrchestration(
            $this->orchestrateCommandHandlers(),
            $this->orchestrateEventHandlers(),
            $orchestrateEventStreamProcessors
        );

        $eventStreamClass = TestEventStream::class;

        $orchestrateLongbow->eventStream($eventStreamClass);

        $this->assertSame($eventStreamClass, $orchestrateEventStreamProcessors->eventStreamClass);
    }

    public function test_exports_orchestration(): void
    {
        $orchestrateCommandHandlers = $this->orchestrateCommandHandlers();
        $orchestrateEventHandlers = $this->orchestrateEventHandlers();
        $orchestrateEventStreamProcessors = $this->orchestrateEventStreamProcessors();

        $orchestrateLongbow = new LongbowOrchestration(
            $orchestrateCommandHandlers,
            $orchestrateEventHandlers,
            $orchestrateEventStreamProcessors
        );

        $directory = $this->createMock(Directory::class);
        $orchestrateLongbow->exportOrchestrationTo($directory);

        $this->assertSame($directory, $orchestrateCommandHandlers->directory);
        $this->assertSame($directory, $orchestrateEventHandlers->directory);
        $this->assertSame($directory, $orchestrateEventStreamProcessors->directory);
    }

    private function orchestrateCommandHandlers(): OrchestrateCommandHandlers&ExportOrchestration
    {
        return new class() implements OrchestrateCommandHandlers, ExportOrchestration {
            public readonly string    $commandClass;
            public readonly string    $commandHandlerClass;
            public readonly Directory $directory;

            public function command(string $commandClass): OrchestrateCommandHandlers
            {
                $this->commandClass = $commandClass;

                return $this;
            }

            public function isHandledBy(string $commandHandlerClass): void
            {
                $this->commandHandlerClass = $commandHandlerClass;
            }

            public function exportOrchestrationTo(Directory $directory): void
            {
                $this->directory = $directory;
            }
        };
    }

    private function orchestrateEventHandlers(): OrchestrateEventHandlers&ExportOrchestration
    {
        return new class() implements OrchestrateEventHandlers, ExportOrchestration {
            public readonly string    $eventClass;
            public readonly string    $eventHandlerClass;
            public readonly Directory $directory;

            public function onEvent(string $eventClass): OrchestrateEventHandlers
            {
                $this->eventClass = $eventClass;

                return $this;
            }

            public function runSynchronously(string $eventHandlerClass): void
            {
                $this->eventHandlerClass = $eventHandlerClass;
            }

            public function exportOrchestrationTo(Directory $directory): void
            {
                $this->directory = $directory;
            }
        };
    }

    private function orchestrateEventStreamProcessors(): OrchestrateEventStreamProcessors&ExportOrchestration
    {
        return new class() implements OrchestrateEventStreamProcessors, ExportOrchestration {
            public readonly string    $eventStreamClass;
            public readonly string    $streamHandlerClass;
            public readonly Directory $directory;

            public function eventStream(string $eventStreamClass): OrchestrateEventStreamProcessors
            {
                $this->eventStreamClass = $eventStreamClass;

                return $this;
            }

            public function isProcessedBy(string $streamProcessorClass): void
            {
                $this->streamHandlerClass = $streamProcessorClass;
            }

            public function exportOrchestrationTo(Directory $directory): void
            {
                $this->directory = $directory;
            }
        };
    }
}

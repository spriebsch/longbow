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

use spriebsch\filesystem\Directory;
use spriebsch\longbow\commands\LongbowOrchestrateCommandHandlers;
use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\LongbowOrchestrateEventHandlers;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\LongbowOrchestrateEventStreamProcessors;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;

final readonly class LongbowOrchestration implements Orchestration, ExportOrchestration
{
    public static function initialize(): self
    {
        return new self(
            new LongbowOrchestrateCommandHandlers,
            new LongbowOrchestrateEventHandlers,
            new LongbowOrchestrateEventStreamProcessors
        );
    }

    public function __construct(
        private readonly OrchestrateCommandHandlers&ExportOrchestration       $orchestrateCommandHandlers,
        private readonly OrchestrateEventHandlers&ExportOrchestration         $orchestrateEventHandlers,
        private readonly OrchestrateEventStreamProcessors&ExportOrchestration $orchestrateEventStreamProcessors
    ) {}

    public function command(string $commandClass): OrchestrateCommandHandlers
    {
        $this->orchestrateCommandHandlers->command($commandClass);

        return $this->orchestrateCommandHandlers;
    }

    public function onEvent(string $eventClass): OrchestrateEventHandlers
    {
        $this->orchestrateEventHandlers->onEvent($eventClass);

        return $this->orchestrateEventHandlers;
    }

    public function eventStream(string $eventStreamClass): OrchestrateEventStreamProcessors
    {
        $this->orchestrateEventStreamProcessors->eventStream($eventStreamClass);

        return $this->orchestrateEventStreamProcessors;
    }

    public function exportOrchestrationTo(Directory $directory): void
    {
        $this->orchestrateCommandHandlers->exportOrchestrationTo($directory);
        $this->orchestrateEventHandlers->exportOrchestrationTo($directory);
        $this->orchestrateEventStreamProcessors->exportOrchestrationTo($directory);
    }
}

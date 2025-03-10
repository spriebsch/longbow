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

use spriebsch\longbow\commands\OrchestrateCommandHandlers;
use spriebsch\longbow\events\OrchestrateEventHandlers;
use spriebsch\longbow\eventStreams\OrchestrateEventStreamProcessors;

interface Orchestration
{
    public function command(string $commandClass): OrchestrateCommandHandlers;

    public function onEvent(string $eventClass): OrchestrateEventHandlers;

    public function eventStream(string $eventStreamClass): OrchestrateEventStreamProcessors;
}

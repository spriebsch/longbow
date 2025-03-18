<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\example;

use spriebsch\eventstore\Event;
use spriebsch\eventstore\Events;
use spriebsch\eventstore\EventWriter;
use spriebsch\longbow\commands\CommandHandler;

class SomeCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly EventWriter $eventWriter,
    ) {}

    public function handle(SomeCommand $command): Event
    {
        $event = SomeEvent::from(SomeId::generate(), $command->payload);

        $this->eventWriter->store(Events::from($event));

        return $event;
    }
}

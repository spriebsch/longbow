<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\commands;

use Exception;
use spriebsch\diContainer\Container;
use spriebsch\eventstore\Event;
use spriebsch\longbow\events\EventDispatcher;

final readonly class LongbowCommandDispatcher implements CommandDispatcher
{
    public function __construct(
        private CommandHandlerMap $handlerMap,
        private Container         $container,
        private EventDispatcher   $eventDispatcher,
    ) {}

    public function handle(Command $command): Event
    {
        try {
            $handler = $this->container->get($this->handlerMap->handlerClassFor($command));
            $event = $handler->handle($command);
            $this->eventDispatcher->dispatch($event);

            return $event;
        } catch (Exception $exception) {
            throw new FailedToDispatchCommandException($command, $exception);
        }
    }
}

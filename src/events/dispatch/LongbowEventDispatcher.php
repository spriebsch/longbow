<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\events;

use spriebsch\diContainer\Container;
use spriebsch\DomainEvent\DomainEvent;

final readonly class LongbowEventDispatcher implements EventDispatcher
{
    public function __construct(
        private EventHandlerMap $eventHandlerMap,
        private Container       $container,
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        foreach ($this->eventHandlerMap->handlerClassesFor($event) as $handlerClass) {
            $handler = $this->container->get($handlerClass);
            assert(method_exists($handler, 'handle'));
            $handler->handle($event);
        }
    }
}

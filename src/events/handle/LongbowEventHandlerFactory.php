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

use spriebsch\longbow\SafeFactory;
use Throwable;

final readonly class LongbowEventHandlerFactory implements EventHandlerFactory
{
    public function __construct(private SafeFactory $factory) {}

    public function createEventHandler(string $class): EventHandler
    {
        try {
            return $this->tryToCreateEventHandler($class);
        } catch (Throwable $exception) {
            throw new FailedToCreateEventHandlerException($class, $exception);
        }
    }

    private function tryToCreateEventHandler(string $class): EventHandler
    {
        $this->ensureClassExists($class);

        $eventHandler = $this->factory->create($class);

        $this->ensureImplementsEventHandlerInterface($eventHandler);
        assert($eventHandler instanceof EventHandler);

        return $eventHandler;
    }

    private function ensureClassExists(string $class): void
    {
        if (!class_exists($class)) {
            throw new EventHandlerDoesNotExistException($class);
        }
    }

    private function ensureImplementsEventHandlerInterface(object $eventHandler): void
    {
        if (
            !in_array(
                EventHandler::class,
                class_implements($eventHandler),
                true
            )
        ) {
            throw new ClassDoesNotImplementEventHandlerInterfaceException($eventHandler::class);
        }
    }
}

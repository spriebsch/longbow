<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\eventStreams;

use ReflectionClass;
use ReflectionMethod;
use spriebsch\eventstore\Event;

final readonly class EventStreamProcessorWrapper
{
    public function __construct(private EventStreamProcessor $eventStreamProcessor) {}

    public function process(Event $event): void
    {
        var_dump('PROCESS');

        $method = $this->onEventMethodNameFor($event);

        $this->ensureHasMethod($this->eventStreamProcessor, $method);
        $this->ensureMethodHasExactlyOneParameter($this->eventStreamProcessor, $method);
        $this->ensureMethodParameterIsTheEvent($this->eventStreamProcessor, $method, $event);

        $this->eventStreamProcessor->{$method}($event);
    }

    private function ensureHasMethod(EventStreamProcessor $eventStreamProcessor, string $method): void
    {
        if (!method_exists($eventStreamProcessor, $method)) {
            throw new EventStreamProcessorHasNoOnEventMethodException($eventStreamProcessor, $method);
        }
    }

    private function ensureMethodHasExactlyOneParameter(EventStreamProcessor $eventStreamProcessor, string $method):
    void
    {
        if (count($this->reflectHandleMethodParameters($eventStreamProcessor, $method)) !== 1) {
            throw new MethodDoesNotHaveExactlyOneParameterException(
                $eventStreamProcessor,
                $method
            );
        }
    }

    private function ensureMethodParameterIsTheEvent(
        EventStreamProcessor $eventStreamProcessor,
        string               $method,
        Event                $event
    ): void
    {
        if ($this->reflectMethodParameterType($eventStreamProcessor, $method) !== $event::class) {
            throw new MethodDoesNotHandleEventException(
                $eventStreamProcessor,
                $method,
                $event
            );
        }
    }

    private function reflectMethodParameterType(object $eventHandler, string $method): ?string
    {
        return $this->reflectHandleMethodParameters($eventHandler, $method)[0]->getType()?->getName();
    }

    private function reflectHandleMethodParameters(object $eventHandler, string $method): array
    {
        return $this->reflectMethod($eventHandler, $method)->getParameters();
    }

    private function reflectMethod(object $eventHandler, string $method): ReflectionMethod
    {
        return (new ReflectionClass($eventHandler))->getMethod($method);
    }

    private function onEventMethodNameFor(Event $event): string
    {
        return 'on' . (new ReflectionClass($event))->getShortName();
    }
}

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

use ReflectionClass;
use ReflectionMethod;
use spriebsch\eventstore\Event;
use spriebsch\filesystem\Directory;
use spriebsch\longbow\orchestration\ExportOrchestration;
use spriebsch\longbow\orchestration\LongbowPHPArraySerializer;
use spriebsch\longbow\orchestration\PHPArraySerializer;

final class LongbowOrchestrateEventHandlers implements OrchestrateEventHandlers, ExportOrchestration
{
    private readonly PHPArraySerializer $serializer;
    private ?string                     $eventClass    = null;
    private array                       $eventHandlers = [];

    public function __construct(?PHPArraySerializer $serializer = null)
    {
        if ($serializer === null) {
            $serializer = new LongbowPHPArraySerializer;
        }

        $this->serializer = $serializer;
    }

    public function onEvent(string $eventClass): self
    {
        $this->ensureEventClassExists($eventClass);
        $this->ensureClassIsInstanceOfEvent($eventClass);

        $this->eventClass = $eventClass;

        return $this;
    }

    public function runSynchronously(string $eventHandlerClass): void
    {
        $this->ensureEventHasBeenSpecifiedFor($eventHandlerClass);

        assert($this->eventClass !== null);

        $this->ensureHandlerClassExists($eventHandlerClass);
        $this->ensureImplementsEventHandlerInterface($eventHandlerClass);
        $this->ensureHasHandleMethod($eventHandlerClass);
        $this->ensureHandleMethodHasExactlyOneParameter($eventHandlerClass);
        $this->ensureHandleMethodParameterIsTheEventToBeHandled($eventHandlerClass);
        $this->ensureHandleMethodHasVoidReturnType($eventHandlerClass);

        // @todo ensure void return type?

        $this->storeHandlerClassFor($this->eventClass, $eventHandlerClass);
    }

    private function ensureEventHasBeenSpecifiedFor(string $eventHandlerClass): void
    {
        if ($this->eventClass === null) {
            throw new NoEventSpecifiedException($eventHandlerClass);
        }
    }

    private function storeHandlerClassFor(string $eventClass, string $eventHandlerClass): void
    {
        $this->eventHandlers[$eventClass][] = $eventHandlerClass;
    }

    private function ensureHandlerClassExists(string $eventHandlerClass)
    {
        if (!class_exists($eventHandlerClass)) {
            throw new EventHandlerClassDoesNotExistException($eventHandlerClass);
        }
    }

    private function ensureImplementsEventHandlerInterface(string $eventHandlerClass): void
    {
        if (!in_array(EventHandler::class, class_implements($eventHandlerClass), true)) {
            throw new ClassDoesNotImplementEventHandlerInterfaceException($eventHandlerClass);
        }
    }

    private function ensureHasHandleMethod(string $eventHandlerClass): void
    {
        if (!method_exists($eventHandlerClass, 'handle')) {
            throw new EventHandlerHasNoHandleMethodException($eventHandlerClass);
        }
    }

    private function ensureHandleMethodHasExactlyOneParameter(string $eventHandlerClass): void
    {
        if (count($this->reflectHandleMethodParameters($eventHandlerClass)) !== 1) {
            throw new HandleMethodDoesNotHaveExactlyOneParameterException($eventHandlerClass);
        }
    }

    private function ensureHandleMethodParameterIsTheEventToBeHandled(string $eventHandlerClass): void
    {
        if ($this->reflectHandleMethodParameters($eventHandlerClass)[0]->getType()?->getName() !== $this->eventClass) {
            throw new HandleMethodParameterIsNotTheEventToBeHandledException(
                $eventHandlerClass,
                $this->eventClass
            );
        }
    }

    private function ensureHandleMethodHasVoidReturnType(string $class): void
    {
        if ($this->reflectHandleMethod($class)->getReturnType() === null) {
            throw new HandleMethodHasNoVoidReturnTypeException($class);
        }
    }

    private function reflectHandleMethod($class): ReflectionMethod
    {
        return (new ReflectionClass($class))->getMethod('handle');
    }

    private function reflectMethod(string $eventHandlerClass, string $method): ReflectionMethod
    {
        return (new ReflectionClass($eventHandlerClass))->getMethod($method);
    }

    private function reflectHandleMethodParameters(string $eventHandlerClass): array
    {
        return $this->reflectMethod($eventHandlerClass, 'handle')->getParameters();
    }

    public function exportOrchestrationTo(Directory $directory): void
    {
        $this->serializer->serialize(
            $this->eventHandlers,
            $directory,
            OrchestrateEventHandlers::SERIALIZATION_FILE
        );
    }

    private function ensureEventClassExists(string $class): void
    {
        if (!class_exists($class)) {
            throw new EventClassDoesNotExistException($class);
        }
    }

    private function ensureClassIsInstanceOfEvent(string $eventClass): void
    {
        if ($this->classIsNoInstanceOfEvent($eventClass)) {
            throw new ClassDoesNotImplementEventInterfaceException($eventClass);
        }
    }

    private function classIsNoInstanceOfEvent(string $eventClass): bool
    {
        return !in_array(Event::class, class_implements($eventClass), true);
    }
}

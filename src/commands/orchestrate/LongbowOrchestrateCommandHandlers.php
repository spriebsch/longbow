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
use ReflectionClass;
use ReflectionMethod;
use spriebsch\eventstore\Event;
use spriebsch\filesystem\Directory;
use spriebsch\longbow\orchestration\ExportOrchestration;
use spriebsch\longbow\orchestration\LongbowPHPArraySerializer;
use spriebsch\longbow\orchestration\PHPArraySerializer;

final class LongbowOrchestrateCommandHandlers implements OrchestrateCommandHandlers, ExportOrchestration
{
    private readonly PHPArraySerializer $serializer;
    private ?string                     $commandClass    = null;
    private array                       $commandHandlers = [];

    public function __construct(?PHPArraySerializer $serializer = null)
    {
        if ($serializer === null) {
            $serializer = new LongbowPHPArraySerializer;
        }

        $this->serializer = $serializer;
    }

    public function command(string $commandClass): self
    {
        try {
            $this->ensureImplementsCommandInterface($commandClass);
            $this->commandClass = $commandClass;

            return $this;
        } catch (Exception $exception) {
            throw new FailedToConfigureCommandException(
                $commandClass,
                $exception
            );
        }
    }

    public function isHandledBy(string $commandHandlerClass): void
    {
        $this->ensureCommandHasBeenSpecifiedFor($commandHandlerClass);
        $this->ensureNoHandlerIsAlreadyConfiguredForCommand();

        try {
            $this->ensureCommandHandlerClassExists($commandHandlerClass);
            $this->ensureImplementsCommandHandlerInterface($commandHandlerClass);
            $this->ensureHasHandleMethod($commandHandlerClass);
            $this->ensureHandleMethodIsPublic($commandHandlerClass);
            $this->ensureHandleMethodHasExactlyOneParameter($commandHandlerClass);
            $this->ensureHandleMethodParameterIsTheCommand($commandHandlerClass);
            $this->ensureHandleMethodHasReturnType($commandHandlerClass);
            $this->ensureHandleMethodReturnsAnEvent($commandHandlerClass);
        } catch (Exception $exception) {
            throw new FailedToConfigureCommandHandlerException(
                $commandHandlerClass,
                $exception
            );
        }

        $this->commandHandlers[$this->commandClass] = $commandHandlerClass;
        $this->commandClass = null;
    }

    public function exportOrchestrationTo(Directory $directory): void
    {
        $this->serializer->serialize(
            $this->commandHandlers,
            $directory,
            OrchestrateCommandHandlers::SERIALIZATION_FILE
        );
    }

    private function ensureImplementsCommandInterface(string $class): void
    {
        $implementedClasses = class_implements($class);

        if ($implementedClasses === false) {
            // @todo throw execption - command class does not exist?
        }

        if (!in_array(
            Command::class,
            $implementedClasses,
            true
        )
        ) {
            throw new DoesNotImplementCommandInterfaceException($class);
        }
    }

    private function ensureCommandHasBeenSpecifiedFor(string $commandHandlerClass): void
    {
        if ($this->commandClass === null) {
            throw new NoCommandSpecifiedException($commandHandlerClass);
        }

        assert($this->commandClass !== null);
    }

    private function ensureCommandHandlerClassExists(string $class): void
    {
        if (!class_exists($class)) {
            throw new CommandHandlerClassDoesNotExistException($class);
        }
    }

    private function ensureImplementsCommandHandlerInterface(string $class): void
    {
        if (
            !in_array(
                CommandHandler::class,
                class_implements($class, true),
                true
            )
        ) {
            throw new ClassDoesNotImplementCommandHandlerInterfaceException($class);
        }
    }

    private function ensureHasHandleMethod(string $class): void
    {
        if (!method_exists($class, 'handle')) {
            throw new CommandHandlerHasNoHandleMethodException($class);
        }
    }

    private function ensureHandleMethodIsPublic(string $class): void
    {
        if (!$this->reflectHandleMethod($class)->isPublic()) {
            throw new CommandHandlerHandleMethodIsNotPublicException($class);
        }
    }

    private function ensureHandleMethodHasExactlyOneParameter(string $class)
    {
        if (count($this->reflectHandleMethodParameters($class)) !== 1) {
            throw new CommandHandlerHandleMethodDoesNotHaveExactlyOneParameterException($class);
        }
    }

    private function ensureHandleMethodParameterIsTheCommand(string $class): void
    {
        if ($this->reflectHandleMethodParameters($class)[0]->getType()?->getName() !== $this->commandClass) {
            throw new ClassDoesNotHandleCommandException(
                $class,
                $this->commandClass
            );
        }
    }

    private function ensureHandleMethodHasReturnType(string $class): void
    {
        if ($this->reflectHandleMethod($class)->getReturnType() === null) {
            throw new CommandHandlerHasNoReturnTypeException($class);
        }
    }

    private function ensureHandleMethodReturnsAnEvent(string $class): void
    {
        if ($this->reflectHandleMethod($class)->getReturnType()->getName() !== Event::class) {
            throw new CommandHandlerDoesNotReturnEventException($class);
        }
    }

    private function ensureNoHandlerIsAlreadyConfiguredForCommand(): void
    {
        if (isset($this->commandHandlers[$this->commandClass])) {
            throw new CommandHandlerHasAlreadyBeenConfiguredException($this->commandClass);
        }
    }

    private function reflectHandleMethod($class): ReflectionMethod
    {
        return (new ReflectionClass($class))->getMethod('handle');
    }

    private function reflectHandleMethodParameters($class): array
    {
        return $this->reflectHandleMethod($class)->getParameters();
    }
}

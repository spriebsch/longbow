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

use spriebsch\longbow\SafeFactory;
use Throwable;

final readonly class LongbowCommandHandlerFactory implements CommandHandlerFactory
{
    public function __construct(private readonly SafeFactory $factory) {}

    public function createCommandHandler(string $class): CommandHandler
    {
        try {
            return $this->tryToCreateCommandHandler($class);
        } catch (Throwable $exception) {
            throw new FailedToCreateCommandHandlerException($class, $exception);
        }
    }

    private function tryToCreateCommandHandler(string $class): CommandHandler
    {
        $this->ensureCommandHandlerExists($class);

        $commandHandler = $this->factory->create($class);

        $this->ensureImplementsCommandHandlerInterface($commandHandler);
        assert($commandHandler instanceof CommandHandler);

        return $commandHandler;
    }

    private function ensureCommandHandlerExists(string $class): void
    {
        if (!class_exists($class)) {
            throw new CommandHandlerDoesNotExistException($class);
        }
    }

    private function ensureImplementsCommandHandlerInterface(object $commandHandler): void
    {
        if (!in_array(
            CommandHandler::class,
            class_implements($commandHandler),
            true
        )
        ) {
            throw new ObjectDoesNotImplementCommandHandlerInterfaceException($commandHandler);
        }
    }
}

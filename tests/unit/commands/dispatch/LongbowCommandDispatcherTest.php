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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventWriter;
use spriebsch\longbow\events\EventDispatcher;
use spriebsch\longbow\SafeFactory;
use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestCommandHandlerFactory;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;
use spriebsch\longbow\tests\TestCommandHandlerThatThrowsException;

#[CoversClass(LongbowCommandDispatcher::class)]
#[CoversClass(FailedToDispatchCommandException::class)]
#[CoversClass(CommandHasNoHandlerException::class)]
#[UsesClass(SafeFactory::class)]
#[UsesClass(LongbowCommandHandlerFactory::class)]
class LongbowCommandDispatcherTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function dispatches_command_to_defined_CommandHandler(): void
    {
        $command = new TestCommand;
        $event = $this->createMock(Event::class);
        $commandHandler = new TestCommandHandlerThatReturnsEvent($event);

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler
        );

        $commandHandlerFactory = $this->factoryThatCreates($commandHandler);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $commandHandlerFactory,
            $eventDispatcher,
        );

        $this->assertSame($event, $dispatcher->handle($command));
    }

    #[Test]
    #[Group('exception')]
    public function CommandHandler_must_be_defined(): void
    {
        $command = new TestCommand;
        $event = $this->createMock(Event::class);
        $commandHandler = new TestCommandHandlerThatReturnsEvent($event);

        $map = $this->createMock(CommandHandlerMap::class);
        $map
            ->method('handlerClassFor')
            ->willThrowException(new CommandHasNoHandlerException($command));

        $commandHandlerFactory = $this->factoryThatCreates($commandHandler);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $commandHandlerFactory,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Test]
    #[Group('exception')]
    public function CommandHandler_must_not_fail(): void
    {
        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler
        );

        $commandHandlerFactory = $this->factoryThatCreates($commandHandler);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $commandHandlerFactory,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Test]
    #[Group('exception')]
    public function EventWriter_must_not_fail(): void
    {
        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler
        );

        $commandHandlerFactory = $this->factoryThatCreates($commandHandler);

        $eventWriter = $this->createMock(EventWriter::class);
        $eventWriter->method('store')->willThrowException(new RuntimeException);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $commandHandlerFactory,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Test]
    #[Group('exception')]
    public function EventDispatcher_must_not_fail(): void
    {
        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler
        );

        $commandHandlerFactory = $this->factoryThatCreates($commandHandler);

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')->willThrowException(new RuntimeException);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $commandHandlerFactory,
            $eventDispatcher
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    private function commandHandlerMapThatHandles(
        Command        $command,
        CommandHandler $commandHandler
    ): CommandHandlerMap&MockObject
    {
        $map = $this->createMock(CommandHandlerMap::class);
        $map
            ->expects($this->once())
            ->method('handlerClassFor')
            ->with($command)
            ->willReturn($commandHandler::class);

        return $map;
    }

    public function factoryThatCreates(CommandHandler $commandHandler): LongbowCommandHandlerFactory
    {
        return new LongbowCommandHandlerFactory(
            new SafeFactory(new TestCommandHandlerFactory($commandHandler))
        );
    }
}

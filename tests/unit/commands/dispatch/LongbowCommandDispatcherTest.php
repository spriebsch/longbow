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
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\diContainer\DIContainer;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventWriter;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\events\EventDispatcher;
use spriebsch\longbow\example\LongbowConfiguration;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;
use spriebsch\longbow\tests\TestCommandHandlerThatThrowsException;

#[CoversClass(LongbowCommandDispatcher::class)]
#[CoversClass(CommandHandlerMap::class)]
#[CoversClass(FailedToDispatchCommandException::class)]
#[CoversClass(CommandHasNoHandlerException::class)]
class LongbowCommandDispatcherTest extends TestCase
{
    #[Group('feature')]
    public function test_dispatches_command_to_defined_CommandHandler(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $command = new TestCommand;
        $event = $this->createMock(Event::class);
        TestCommandHandlerThatReturnsEvent::willReturn($event);

        $map = CommandHandlerMap::fromArray([$command::class => TestCommandHandlerThatReturnsEvent::class]);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher($map, $container, $eventDispatcher);

        $this->assertSame($event, $dispatcher->handle($command));
    }

    #[Group('exception')]
    public function test_CommandHandler_must_be_defined(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $command = new TestCommand;
        $event = $this->createMock(Event::class);

        $map = CommandHandlerMap::fromArray([]);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $container,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Group('exception')]
    public function test_CommandHandler_must_not_fail(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler,
        );

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $container,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Group('exception')]
    public function test_EventWriter_must_not_fail(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler,
        );

        $eventWriter = $this->createMock(EventWriter::class);
        $eventWriter->method('store')->willThrowException(new RuntimeException);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $container,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    #[Group('exception')]
    public function test_EventDispatcher_must_not_fail(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $command = new TestCommand;
        $commandHandler = new TestCommandHandlerThatThrowsException;

        $map = $this->commandHandlerMapThatHandles(
            $command,
            $commandHandler,
        );

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')->willThrowException(new RuntimeException);

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher = new LongbowCommandDispatcher(
            $map,
            $container,
            $eventDispatcher,
        );

        $this->expectException(FailedToDispatchCommandException::class);

        $dispatcher->handle($command);
    }

    private function commandHandlerMapThatHandles(
        Command        $command,
        CommandHandler $commandHandler,
    ): CommandHandlerMap
    {
        return CommandHandlerMap::fromArray(
            [$command::class => $commandHandler::class],
        );
    }
}

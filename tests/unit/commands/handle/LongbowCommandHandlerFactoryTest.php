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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\eventstore\Event;
use spriebsch\longbow\SafeFactory;
use spriebsch\longbow\tests\TestCommandHandlerFactory;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;

#[CoversClass(LongbowCommandHandlerFactory::class)]
#[CoversClass(CommandHandlerDoesNotExistException::class)]
#[CoversClass(FailedToCreateCommandHandlerException::class)]
#[CoversClass(ObjectDoesNotImplementCommandHandlerInterfaceException::class)]
#[UsesClass(SafeFactory::class)]
class LongbowCommandHandlerFactoryTest extends TestCase
{
    #[Group('feature')]
    public function test_creates_CommandHandler(): void
    {
        $event = $this->createMock(Event::class);
        $commandHandler = new TestCommandHandlerThatReturnsEvent($event);

        $factory = new LongbowCommandHandlerFactory(
            new SafeFactory(new TestCommandHandlerFactory($commandHandler))
        );

        $handler = $factory->createCommandHandler($commandHandler::class);

        $this->assertInstanceOf(TestCommandHandlerThatReturnsEvent::class, $handler);
    }

    #[Group('exception')]
    public function test_CommandHandler_must_exist(): void
    {
        $factory = new LongbowCommandHandlerFactory(
            new SafeFactory(new TestCommandHandlerFactory(null))
        );

        $this->expectException(FailedToCreateCommandHandlerException::class);

        $factory->createCommandHandler('does-not-exist');
    }

    #[Group('exception')]
    public function test_CommandHandler_factory_method_must_not_throw_exception(): void
    {
        $factory = new LongbowCommandHandlerFactory(
            new SafeFactory(
                new class() {
                    public function testCommandHandlerThatReturnsEvent(): TestCommandHandlerThatReturnsEvent
                    {
                        throw new RuntimeException('the-exception-message');
                    }
                }
            )
        );

        $this->expectException(FailedToCreateCommandHandlerException::class);
        $this->expectExceptionMessage('the-exception-message');

        $factory->createCommandHandler(TestCommandHandlerThatReturnsEvent::class);
    }

    #[Group('exception')]
    public function test_created_object_must_implement_interface_CommandHandler(): void
    {
        $factory = new LongbowCommandHandlerFactory(
            new SafeFactory(
                new class() {
                    public function testCommandHandlerThatReturnsEvent()
                    {
                        return new class() {
                        };
                    }
                }
            )
        );

        $this->expectException(FailedToCreateCommandHandlerException::class);
        $this->expectExceptionMessage('does not implement');

        $factory->createCommandHandler(TestCommandHandlerThatReturnsEvent::class);
    }
}

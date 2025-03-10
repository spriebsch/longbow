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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\longbow\FactoryHasNoSuchMethodException;
use spriebsch\longbow\FactoryMethodIsNotPublicException;
use spriebsch\longbow\SafeFactory;
use spriebsch\longbow\tests\TestEventHandler;

#[CoversClass(LongbowEventHandlerFactory::class)]
#[CoversClass(FailedToCreateEventHandlerException::class)]
#[CoversClass(ClassDoesNotImplementEventHandlerInterfaceException::class)]
#[CoversClass(FactoryHasNoSuchMethodException::class)]
#[CoversClass(FactoryMethodIsNotPublicException::class)]
#[CoversClass(EventHandlerClassDoesNotExistException::class)]
#[CoversClass(EventHandlerDoesNotExistException::class)]
#[UsesClass(SafeFactory::class)]
class LongbowEventHandlerFactoryTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function locates_event_handler(): void
    {
        $applicationFactory = new class() {
            public function testEventHandler(): TestEventHandler
            {
                return new TestEventHandler;
            }
        };

        $factory = new LongbowEventHandlerFactory(new SafeFactory($applicationFactory));

        $this->assertInstanceOf(
            TestEventHandler::class,
            $factory->createEventHandler(TestEventHandler::class)
        );
    }

    #[Test]
    #[Group('exception')]
    public function EventHandler_must_exist(): void
    {
        $factory = new LongbowEventHandlerFactory(
            new SafeFactory(
                new class() {
                }
            )
        );

        $this->expectException(FailedToCreateEventHandlerException::class);

        $factory->createEventHandler('the-handler');
    }

    #[Test]
    #[Group('exception')]
    public function application_factory_must_have_appropriate_create_method(): void
    {
        $factory = new LongbowEventHandlerFactory(
            new SafeFactory(
                new class() {
                }
            )
        );

        $this->expectException(FailedToCreateEventHandlerException::class);

        $factory->createEventHandler(TestEventHandler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandlerFactory_method_must_be_public(): void
    {
        $applicationFactory = new class() {
            protected function testEventHandler(): TestEventHandler
            {
                return new TestEventHandler;
            }
        };

        $factory = new LongbowEventHandlerFactory(new SafeFactory($applicationFactory));

        $this->expectException(FailedToCreateEventHandlerException::class);

        $factory->createEventHandler(TestEventHandler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandlerFactory_method_must_not_be_private(): void
    {
        $applicationFactory = new class() {
            private function testEventHandler(): TestEventHandler
            {
                return new TestEventHandler;
            }
        };

        $factory = new LongbowEventHandlerFactory(new SafeFactory($applicationFactory));

        $this->expectException(FailedToCreateEventHandlerException::class);

        $factory->createEventHandler(TestEventHandler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandlerFactory_must_have_parameters(): void
    {
        $applicationFactory = new class() {
            public function testEventHandler($unexpectedParameter): TestEventHandler
            {
                return new TestEventHandler;
            }
        };

        $factory = new LongbowEventHandlerFactory(new SafeFactory($applicationFactory));

        $this->expectException(FailedToCreateEventHandlerException::class);

        $factory->createEventHandler(TestEventHandler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandler_must_implement_EventHandler_interface(): void
    {
        $applicationFactory = new class() {
            public function testEventHandler()
            {
                return new class() {
                };
            }
        };

        $factory = new LongbowEventHandlerFactory(new SafeFactory($applicationFactory));

        $this->expectException(FailedToCreateEventHandlerException::class);
        $this->expectExceptionMessage('does not implement interface');

        $factory->createEventHandler(TestEventHandler::class);
    }
}

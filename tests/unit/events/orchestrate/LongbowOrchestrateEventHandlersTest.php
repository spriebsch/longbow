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
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\Directory;
use spriebsch\longbow\commands\CommandHandlerHandleMethodDoesNotHaveExactlyOneParameterException;
use spriebsch\longbow\orchestration\PHPArraySerializer;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventHandler;
use stdClass;

#[CoversClass(LongbowOrchestrateEventHandlers::class)]
#[CoversClass(ClassDoesNotImplementEventHandlerInterfaceException::class)]
#[CoversClass(HandleMethodDoesNotHaveExactlyOneParameterException::class)]
#[CoversClass(EventHandlerHasNoHandleMethodException::class)]
#[CoversClass(EventHandlerHasNoHandleMethodException::class)]
#[CoversClass(HandleMethodHasNoVoidReturnTypeException::class)]
#[CoversClass(NoEventSpecifiedException::class)]
#[CoversClass(HandleMethodParameterIsNotTheEventToBeHandledException::class)]
#[CoversClass(EventClassDoesNotExistException::class)]
#[CoversClass(ClassDoesNotImplementEventInterfaceException::class)]
#[CoversClass(EventHandlerClassDoesNotExistException::class)]
class LongbowOrchestrateEventHandlersTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function EventHandler_can_be_configured(): void
    {
        $directory = $this->createMock(Directory::class);
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $expectation = [TestEvent::class => [TestEventHandler::class]];

        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($expectation, $directory, OrchestrateEventHandlers::SERIALIZATION_FILE);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously(TestEventHandler::class);

        $orchestration->exportOrchestrationTo($directory);
    }

    #[Test]
    #[Group('exception')]
    public function Event_must_be_set_before_configuring_EventHandler(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $this->expectException(NoEventSpecifiedException::class);

        $orchestration->runSynchronously(TestEventHandler::class);
    }

    #[Test]
    #[Group('exception')]
    public function event_class_must_exist(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $this->expectException(EventClassDoesNotExistException::class);

        $orchestration->onEvent('does-not-exist');
    }

    #[Test]
    #[Group('exception')]
    public function event_must_implement_Event_interface(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $this->expectException(ClassDoesNotImplementEventInterfaceException::class);

        $orchestration->onEvent(stdClass::class);
    }

    #[Test]
    #[Group('exception')]
    public function event_handler_class_must_exist(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $this->expectException(EventHandlerClassDoesNotExistException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously('does-not-exist');
    }

    #[Test]
    #[Group('exception')]
    public function event_handler_must_implement_event_handler_interface(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $this->expectException(ClassDoesNotImplementEventHandlerInterfaceException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously(stdClass::class);
    }

    #[Test]
    #[Group('exception')]
    public function event_handler_has_no_handle_method(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $handler = new class() implements EventHandler {
        };

        $this->expectException(EventHandlerHasNoHandleMethodException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously($handler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandler_handle_method_must_have_void_return_type(): void
    {
        $orchestration = new LongbowOrchestrateEventHandlers;

        $handler = $this->handlerWithoutReturnTypeToHandleMethod();

        $this->expectException(HandleMethodHasNoVoidReturnTypeException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously($handler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandler_handle_method_must_have_exactly_one_parameter(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $handler = new class() implements EventHandler {
            public function handle(TestEvent $event, $additionalParameter) {}
        };

        $this->expectException(HandleMethodDoesNotHaveExactlyOneParameterException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously($handler::class);
    }

    #[Test]
    #[Group('exception')]
    public function EventHandler_handle_method_parameter_must_be_the_specific_event(): void
    {
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $handler = new class() implements EventHandler {
            public function handle($notTheEventAsParameter) {}
        };

        $this->expectException(HandleMethodParameterIsNotTheEventToBeHandledException::class);

        $orchestration
            ->onEvent(TestEvent::class)
            ->runSynchronously($handler::class);
    }

    #[Test]
    #[Group('feature')]
    public function event_handler_map_can_be_exported(): void
    {
        $directory = $this->createMock(Directory::class);
        $serializer = $this->createMock(PHPArraySerializer::class);
        $orchestration = new LongbowOrchestrateEventHandlers($serializer);

        $expectation = [TestEvent::class => [TestEventHandler::class]];

        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($expectation, $directory, OrchestrateEventHandlers::SERIALIZATION_FILE);

        $orchestration->onEvent(TestEvent::class)->runSynchronously(TestEventHandler::class);

        $orchestration->exportOrchestrationTo($directory);
    }

    private function handlerWithoutReturnTypeToHandleMethod(): EventHandler
    {
        return new class implements EventHandler {
            public function handle(TestEvent $event)
            {
            }
        };
    }

}

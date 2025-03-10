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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\uuid\UUID;

#[CoversClass(EventStreamProcessorWrapper::class)]
#[CoversClass(EventStreamProcessorHasNoOnEventMethodException::class)]
#[CoversClass(MethodDoesNotHaveExactlyOneParameterException::class)]
#[CoversClass(MethodDoesNotHandleEventException::class)]
class EventStreamProcessorWrapperTest extends TestCase
{
    #[Group('feature')]
    public function test_calls_specific_onEvent_method_of_EventStreamProcessor(): void
    {
        $event = new TestEvent;
        $processor = new class() implements EventStreamProcessor {
            private array $calls = [];

            public function calls(): array
            {
                return $this->calls;
            }

            public static function id(): UUID
            {
                return UUID::generate();
            }

            public function onTestEvent(TestEvent $event): void
            {
                $this->calls[] = $event;
            }
        };

        $method = new EventStreamProcessorWrapper($processor);
        $method->process($event);

        $this->assertEquals([$event], $processor->calls());
    }

    #[Group('exception')]
    public function test_EventStreamProcessor_must_have_specific_onEvent_method_for_given_event(): void
    {
        $event = new TestEvent;
        $eventHandler = new class() implements EventStreamProcessor {
            public static function id(): UUID
            {
                return UUID::generate();
            }
        };

        $this->expectException(EventStreamProcessorHasNoOnEventMethodException::class);

        $method = new EventStreamProcessorWrapper($eventHandler);
        $method->process($event);
    }

    #[Group('exception')]
    public function test_onEvent_method_in_EventStreamProcessor_must_have_at_least_one_parameter(): void
    {
        $event = new TestEvent;
        $eventHandler = new class() implements EventStreamProcessor {
            public static function id(): UUID
            {
                return UUID::generate();
            }

            public function onTestEvent(): void {}
        };

        $this->expectException(MethodDoesNotHaveExactlyOneParameterException::class);

        $method = new EventStreamProcessorWrapper($eventHandler);
        $method->process($event);
    }

    #[Group('exception')]
    public function test_onEvent_method_in_EventStreamProcessor_must_have_no_more_than_one_parameters(): void
    {
        $event = new TestEvent;
        $eventHandler = new class() implements EventStreamProcessor {
            public static function id(): UUID
            {
                return UUID::generate();
            }

            public function onTestEvent(TestEvent $event, $additionalParameter): void {}
        };

        $this->expectException(MethodDoesNotHaveExactlyOneParameterException::class);

        $method = new EventStreamProcessorWrapper($eventHandler);
        $method->process($event);
    }

    #[Group('exception')]
    public function
    test_onEvent_method_parameter_in_EventStreamProcessor_must_be_the_handled_event(): void
    {
        $event = new TestEvent;
        $eventHandler = new class() implements EventStreamProcessor {
            public static function id(): UUID
            {
                return UUID::generate();
            }

            public function onTestEvent($oneParameterWithWrongType): void {}
        };

        $this->expectException(MethodDoesNotHandleEventException::class);

        $method = new EventStreamProcessorWrapper($eventHandler);
        $method->process($event);
    }
}

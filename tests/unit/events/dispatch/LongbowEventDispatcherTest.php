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
use PHPUnit\Framework\TestCase;
use spriebsch\eventstore\Event;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventHandler;
use spriebsch\longbow\tests\TestEventHandlerFactory;

#[CoversClass(LongbowEventDispatcher::class)]
class LongbowEventDispatcherTest extends TestCase
{
    #[Group('feature')]
    public function test_dispatches_to_EventHandler(): void
    {
        $eventHandlerMap = new class implements EventHandlerMap {
            public function handlerClassesFor(Event $event): array
            {
                return [TestEventHandler::class, TestEventHandler::class];
            }
        };

        $testEventHandler = new TestEventHandler;

        $eventHandlerFactory = new TestEventHandlerFactory($testEventHandler);

        $dispatcher = new LongbowEventDispatcher(
            $eventHandlerMap, $eventHandlerFactory
        );

        $dispatcher->dispatch(new TestEvent);

        $this->assertCount(2, $testEventHandler->calls());
    }
}

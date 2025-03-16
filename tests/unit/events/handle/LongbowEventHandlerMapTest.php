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
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventHandler;

#[CoversClass(EventHandlerMap::class)]
#[CoversClass(HandlerMapDoesNotReturnArrayException::class)]
#[CoversClass(HandlerMapElementIsNoArrayException::class)]
class LongbowEventHandlerMapTest extends TestCase
{
    #[Group('feature')]
    public function test_provides_EventHandler_class_for_given_Event(): void
    {
        $map = EventHandlerMap::fromArray([TestEvent::class => [TestEventHandler::class]]);

        $this->assertSame(
            [TestEventHandler::class],
            $map->handlerClassesFor(new TestEvent)
        );
    }

    #[Group('feature')]
    public function test_EventHandler_must_be_configured(): void
    {
        $map = EventHandlerMap::fromArray([]);

        $this->assertSame([], $map->handlerClassesFor(new TestEvent));
    }

    #[Group('exception')]
    public function test_stored_map_element_must_be_array(): void
    {
        $map = EventHandlerMap::fromArray([TestEvent::class => 'no-array']);

        $this->expectException(HandlerMapElementIsNoArrayException::class);

        $map->handlerClassesFor(new TestEvent);
    }
}

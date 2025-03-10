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
use spriebsch\filesystem\FakeFile;
use spriebsch\filesystem\File;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventHandler;

#[CoversClass(LongbowEventHandlerMap::class)]
#[CoversClass(HandlerMapDoesNotReturnArrayException::class)]
#[CoversClass(HandlerMapElementIsNoArrayException::class)]
class LongbowEventHandlerMapTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function provides_EventHandler_class_for_given_Event(): void
    {
        $map = new LongbowEventHandlerMap(
            new FakeFile('the-filename', 'the-content', $this->testEventMap())
        );

        $this->assertSame(
            [TestEventHandler::class],
            $map->handlerClassesFor(new TestEvent)
        );
    }

    #[Test]
    #[Group('feature')]
    public function loads_map_only_once(): void
    {
        $file = $this->createMock(File::class);
        $file
            ->expects($this->once())
            ->method('require')
            ->willReturn($this->testEventMap());

        $map = new LongbowEventHandlerMap($file);

        $map->handlerClassesFor(new TestEvent);
        $map->handlerClassesFor(new TestEvent);
    }

    #[Test]
    #[Group('feature')]
    public function EventHandler_must_be_configured(): void
    {
        $map = new LongbowEventHandlerMap(
            new FakeFile('the-filename', 'no-content', [])
        );

        $this->assertSame([], $map->handlerClassesFor(new TestEvent));
    }

    #[Test]
    #[Group('exception')]
    public function stored_map_must_be_array(): void
    {
        $map = new LongbowEventHandlerMap(
            new FakeFile('the-filename', 'no-content', 'no-array')
        );

        $this->expectException(HandlerMapDoesNotReturnArrayException::class);

        $map->handlerClassesFor(new TestEvent);
    }

    #[Test]
    #[Group('exception')]
    public function stored_map_element_must_be_array(): void
    {
        $map = new LongbowEventHandlerMap(
            Filesystem::from(__DIR__ . '/../../../fixtures/InvalidEventHandlerMap.php')
        );

        $this->expectException(HandlerMapElementIsNoArrayException::class);

        $map->handlerClassesFor(new TestEvent);
    }

    private function testEventMap(): array
    {
        return [TestEvent::class => [TestEventHandler::class]];
    }
}

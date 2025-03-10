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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\FakeFile;
use spriebsch\filesystem\File;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\tests\TestEventStream;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(LongbowEventStreamProcessorMap::class)]
#[CoversClass(EventStreamProcessorMapDoesNotReturnArrayException::class)]
#[CoversClass(EventStreamProcessorMapElementIsNoArrayException::class)]
class LongbowEventStreamProcessorMapTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function returns_array_of_configured_stream_processors(): void
    {
        $file = Filesystem::from(
            __DIR__ . '/../../../fixtures/EventStreamProcessorsMap.php'
        );

        $eventStreamProcessorMap = new LongbowEventStreamProcessorMap($file);

        $this->assertEquals(
            $file->require(),
            $eventStreamProcessorMap->streams()
        );
    }

    #[Test]
    #[Group('feature')]
    public function empty_array_when_no_processors_configured(): void
    {
        $file = new FakeFile('fakeMap.php', 'no-content', []);

        $eventStreamProcessorMap = new LongbowEventStreamProcessorMap($file);

        $this->assertSame(
            [],
            $eventStreamProcessorMap->streams()
        );
    }

    #[Test]
    #[Group('exception')]
    public function map_must_be_array(): void
    {
        $file = new FakeFile('fakeMap.php', 'no-content', 'not-an-array');

        $eventStreamProcessorMap = new LongbowEventStreamProcessorMap($file);

        $this->expectException(EventStreamProcessorMapDoesNotReturnArrayException::class);

        $eventStreamProcessorMap->streams();
    }

    #[Test]
    #[Group('exception')]
    public function exception_element_must_be_array(): void
    {
        $map = [
            TestEventStream::class => 'not-an-array'
        ];

        $file = new FakeFile('fakeMap.php', 'no-content', $map);

        $eventStreamProcessorMap = new LongbowEventStreamProcessorMap($file);

        $this->expectException(EventStreamProcessorMapElementIsNoArrayException::class);

        $eventStreamProcessorMap->streams();
    }

    #[Test]
    #[Group('feature')]
    public function map_is_loaded_only_once(): void
    {
        $map = require __DIR__ . '/../../../fixtures/EventStreamProcessorsMap.php';

        $file = $this->createMock(File::class);
        $file->expects($this->once())
             ->method('require')
             ->willReturn($map);

        $eventStreamProcessorMap = new LongbowEventStreamProcessorMap($file);

        $eventStreamProcessorMap->streams();
        $eventStreamProcessorMap->streams();
    }
}

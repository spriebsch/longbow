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
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\tests\TestEventStream;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(EventStreamProcessorMap::class)]
#[CoversClass(EventStreamProcessorMapElementIsNoArrayException::class)]
class EventStreamProcessorMapTest extends TestCase
{
    #[Group('feature')]
    public function test_returns_array_of_configured_stream_processors(): void
    {
        $file = Filesystem::from(
            __DIR__ . '/../../../fixtures/EventStreamProcessorsMap.php'
        );

        $eventStreamProcessorMap = EventStreamProcessorMap::fromFile($file);

        $this->assertEquals(
            $file->require(),
            $eventStreamProcessorMap->streams()
        );
    }

    #[Group('feature')]
    public function test_empty_array_when_no_processors_configured(): void
    {
        $eventStreamProcessorMap = EventStreamProcessorMap::fromArray([]);

        $this->assertSame([], $eventStreamProcessorMap->streams());
    }

    #[Group('exception')]
    public function test_exception_element_must_be_array(): void
    {
        $map = [
            TestEventStream::class => 'not-an-array'
        ];

        $this->expectException(EventStreamProcessorMapElementIsNoArrayException::class);

        EventStreamProcessorMap::fromArray($map);
    }

    #[Group('exception')]
    public function test_exception_element_key_must_be_string(): void
    {
        $map = [
            TestEventStream::class => [TestEventStreamProcessor::class]
        ];

        $this->expectException(EventStreamProcessorMapElementHasNoUUIDException::class);

        EventStreamProcessorMap::fromArray($map);
    }
}

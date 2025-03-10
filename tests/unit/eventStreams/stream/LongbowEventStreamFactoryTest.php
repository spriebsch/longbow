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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\longbow\SafeFactory;
use spriebsch\longbow\tests\TestEventStream;

#[CoversClass(LongbowEventStreamFactory::class)]
#[CoversClass(EventStreamClassDoesNotExistException::class)]
#[UsesClass(SafeFactory::class)]
class LongbowEventStreamFactoryTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function creates_EventStream(): void
    {
        $factory = new LongbowEventStreamFactory(
            new SafeFactory(
                new class() {
                    public function testEventStream(): TestEventStream
                    {
                        return new TestEventStream;
                    }
                }
            )
        );

        $stream = $factory->createEventStream(TestEventStream::class);

        $this->assertInstanceOf(TestEventStream::class, $stream);
    }

    #[Test]
    #[Group('exception')]
    public function event_stream_must_exist(): void
    {
        $factory = new LongbowEventStreamFactory(
            new SafeFactory(
                new class() {
                }
            )
        );

        $this->expectException(EventStreamClassDoesNotExistException::class);

        $factory->createEventStream('does-not-exist');
    }
}

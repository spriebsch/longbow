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
use spriebsch\longbow\SafeFactory;
use spriebsch\longbow\tests\TestEventStreamProcessor;
use spriebsch\uuid\UUID;

#[CoversClass(LongbowEventStreamProcessorFactory::class)]
#[CoversClass(SafeFactory::class)]
#[CoversClass(FailedToCreateEventStreamProcessorException::class)]
#[CoversClass(EventStreamProcessorDoesNotExistException::class)]
#[CoversClass(ClassDoesNotImplementEventStreamProcessorInterfaceException::class)]
class EventStreamProcessorFactoryTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function locates_event_handler(): void
    {
        $applicationFactory = new class() {
            public function testEventStreamProcessor(): TestEventStreamProcessor
            {
                return new TestEventStreamProcessor;
            }
        };

        $factory = new LongbowEventStreamProcessorFactory(new SafeFactory($applicationFactory));

        $this->assertInstanceOf(
            EventStreamProcessor::class,
            $factory->createEventStreamProcessor(
                UUID::generate(), TestEventStreamProcessor::class
            )
        );
    }

    #[Test]
    #[Group('exception')]
    public function class_must_exist(): void
    {
        $factory = new LongbowEventStreamProcessorFactory(
            new SafeFactory(
                new class() {
                }
            )
        );

        $this->expectException(FailedToCreateEventStreamProcessorException::class);

        $factory->createEventStreamProcessor(UUID::generate(), 'does-not-exist');
    }

    #[Test]
    #[Group('exception')]
    public function object_must_implement_EventStreamProcessor_interface(): void
    {
        $applicationFactory = new class() {
            public function testEventStreamProcessor()
            {
                return new class() {
                };
            }
        };

        $factory = new LongbowEventStreamProcessorFactory(new SafeFactory($applicationFactory));

        $this->expectException(FailedToCreateEventStreamProcessorException::class);
        $this->expectExceptionMessage('does not implement interface');

        $factory->createEventStreamProcessor(UUID::generate(), TestEventStreamProcessor::class);
    }
}

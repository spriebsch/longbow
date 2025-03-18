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
use PHPUnit\Framework\TestCase;
use spriebsch\diContainer\DIContainer;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\Events;
use spriebsch\eventstore\EventWriter;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\DispatchTestEvent;
use spriebsch\longbow\example\ApplicationConfiguration;
use spriebsch\longbow\LongbowContainer;
use spriebsch\longbow\SqliteStreamPosition;
use spriebsch\longbow\tests\DispatcherTestEventStream;
use spriebsch\longbow\tests\TestApplicationFactory;
use spriebsch\longbow\tests\TestCorrelationId;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(LongbowEventStreamDispatcher::class)]
class LongbowEventStreamDispatcherTest extends TestCase
{
    public function test_some(): void
    {
        $toEventId = fn(Event $event) => $event->id()->asString();

        $diContainer = new DiContainer(new ApplicationConfiguration, TestApplicationFactory::class);
        $container = new LongbowContainer(
            new FakeDirectory('/fake'),
            Filesystem::from(__DIR__ . '/../../../stubs/events.php'),
            ':memory:',
            ':memory:',
            $diContainer,
        );

        $events = [
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-1'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-2'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-3'),
        ];

        $expected = array_map($toEventId, $events);

        $eventWriter = $container->get(EventWriter::class);
        $eventWriter->store(Events::from(...$events));

        $streamPosition = $container->get(SqliteStreamPosition::class);
        $processorMap = EventStreamProcessorMap::fromArray(
            [
                DispatcherTestEventStream::class =>
                    [
                        TestEventStreamProcessor::id()->asString() => TestEventStreamProcessor::class,
                    ],
            ],
        );

        $dispatcher = new LongbowEventStreamDispatcher($processorMap, $streamPosition, $container);
        $dispatcher->run();

        /** @var TestEventStreamProcessor $processor */
        $processor = $container->get(TestEventStreamProcessor::class, TestEventStreamProcessor::id());

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventId, $processedEvents);

        $this->assertSame($expected, $processedEvents);
    }
}

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
use RuntimeException;
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
    public function test_processes_events(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        /** @var TestEventStreamProcessor $processor */
        $processor = $fixture->container->get(TestEventStreamProcessor::class);

        $fixture->dispatcher->run();

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $this->assertSame($expected, $processedEvents);
    }

    public function test_fail(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        /** @var TestEventStreamProcessor $processor */
        $processor = $fixture->container->get(TestEventStreamProcessor::class);
        $processor->failOn(3);

        try {
            $fixture->dispatcher->run();
        } catch (RuntimeException $exception) {
        }

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $expected = array_slice($expected, 0, -1);

        $this->assertSame($expected, $processedEvents);
    }
}

final readonly class EventStreamDispatcherTestFixture
{
    public LongbowEventStreamDispatcher $dispatcher;
    public LongbowContainer $container;
    public array $events;

    public function __construct()
    {
        $diContainer = new DiContainer(new ApplicationConfiguration, TestApplicationFactory::class);
        $this->container = new LongbowContainer(
            new FakeDirectory('/fake'),
            Filesystem::from(__DIR__ . '/../../../stubs/events.php'),
            ':memory:',
            ':memory:',
            $diContainer,
        );

        $this->events = [
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-1'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-2'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-3'),
        ];

        $eventWriter = $this->container->get(EventWriter::class);
        $eventWriter->store(Events::from(...$this->events));

        $streamPosition = $this->container->get(SqliteStreamPosition::class);
        $processorMap = EventStreamProcessorMap::fromArray(
            [
                DispatcherTestEventStream::class =>
                    [
                        TestEventStreamProcessor::id()->asString() => TestEventStreamProcessor::class,
                    ],
            ],
        );

        $this->dispatcher = new LongbowEventStreamDispatcher($processorMap, $streamPosition, $this->container);
    }
}

<?php declare(strict_types=1);

namespace spriebsch\longbow\eventStreams;

use spriebsch\diContainer\Container;
use spriebsch\diContainer\DIContainer;
use spriebsch\eventstore\EventFactory;
use spriebsch\eventstore\Events;
use spriebsch\eventstore\EventWriter;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\DispatchTestEvent;
use spriebsch\longbow\example\LongbowConfiguration;
use spriebsch\longbow\LongbowFactory;
use spriebsch\longbow\SqliteStreamPosition;
use spriebsch\longbow\StreamPosition;
use spriebsch\longbow\tests\DispatcherTestEventStream;
use spriebsch\longbow\tests\TestApplicationFactory;
use spriebsch\longbow\tests\TestCorrelationId;
use spriebsch\longbow\tests\TestEventStreamProcessor;

final readonly class EventStreamDispatcherTestFixture
{
    public LongbowEventStreamDispatcher $dispatcher;
    public Container $container;
    public array $events;

    public function __construct()
    {
        $eventMap = Filesystem::from(__DIR__ . '/../stubs/events.php');
        EventFactory::configureWith($eventMap->require());

        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => new FakeDirectory('/fake'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ]
        );

        $this->container = new DiContainer(
            $configuration,
            TestApplicationFactory::class,
            LongbowFactory::class,
        );

        $this->events = [
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-1'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-2'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-3'),
        ];

        $eventWriter = $this->container->get(EventWriter::class);
        $eventWriter->store(Events::from(...$this->events));

        $streamPosition = $this->container->get(StreamPosition::class);
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

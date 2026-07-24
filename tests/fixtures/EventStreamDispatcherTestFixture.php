<?php declare(strict_types=1);

namespace spriebsch\longbow\eventStreams;

use spriebsch\sequora\EventWriter;
use spriebsch\sequora\EventQuery;
use spriebsch\sequora\EventReader;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\DispatchTestEvent;
use spriebsch\longbow\example\LongbowConfiguration;
use spriebsch\longbow\Longbow;
use spriebsch\longbow\orchestration\LongbowOrchestration;
use spriebsch\longbow\tests\DispatcherTestEventStream;
use spriebsch\longbow\tests\TestApplicationFactory;
use spriebsch\longbow\tests\TestCorrelationId;
use spriebsch\longbow\tests\TestEventStreamProcessor;

final readonly class EventStreamDispatcherTestFixture
{
    public array $events;
    public array $eventIds;

    public function __construct()
    {
        $orchestrationDirectory = Filesystem::from(__DIR__ . '/../../data');

        $orchestrationDirectory->deleteFile('_LongbowCommandHandlers.php');
        $orchestrationDirectory->deleteFile('_LongbowEventHandlers.php');
        $orchestrationDirectory->deleteFile('_LongbowEventStreamProcessors.php');

        $orchestration = LongbowOrchestration::initialize();
        $orchestration
            ->eventStream(DispatcherTestEventStream::class)
            ->isProcessedBy(TestEventStreamProcessor::class);

        $orchestration->exportOrchestrationTo($orchestrationDirectory);

        $eventMap = Filesystem::from(__DIR__ . '/../stubs/events.php');

        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => $orchestrationDirectory,
                'topicMap' => $eventMap,
                'sequoraDatabase' => ':memory:',
                'longbowDatabase' => ':memory:',
            ]
        );

        Longbow::reset();
        Longbow::configure(
            $configuration,
            TestApplicationFactory::class,
        );

        $this->events = [
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-1'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-2'),
            DispatchTestEvent::from(TestCorrelationId::generate(), 'payload-3'),
        ];

        $eventWriter = Longbow::container()->get(EventWriter::class);
        $eventWriter->store(...$this->events);

        /** @var EventReader $eventReader */
        $eventReader = Longbow::container()->get(EventReader::class);
        $this->eventIds = array_map(
            static fn($envelope) => $envelope->eventId(),
            $eventReader->query(EventQuery::from())->envelopes(),
        );
    }
}

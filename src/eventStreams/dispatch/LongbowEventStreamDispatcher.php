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

use spriebsch\eventstore\Events;
use spriebsch\eventstore\EventStream;
use spriebsch\longbow\ExclusiveLock;
use spriebsch\longbow\StreamPositionReader;
use spriebsch\longbow\StreamPositionWriter;
use spriebsch\uuid\UUID;

final class LongbowEventStreamDispatcher implements EventStreamDispatcher
{
    private ?int $limit;

    public function __construct(
        private readonly EventStreamProcessorMap     $streamProcessorMap,
        private readonly ExclusiveLock               $exclusiveLock,
        private readonly StreamPositionReader        $streamPositionReader,
        private readonly StreamPositionWriter        $streamPositionWriter,
        private readonly EventStreamProcessorFactory $eventStreamProcessorFactory,
        private readonly EventStreamFactory          $eventStreamFactory
    ) {}

    public function run(?int $limit = null): void
    {
        $this->limit = $limit;

        // $this->exclusiveLock->acquireLock();

        // @todo make sure to release lock on fail

        foreach ($this->streamProcessorMap->streams() as $eventStreamClass => $processors) {
            foreach ($processors as $processorId => $processorClass) {
                $this->runEventStreamProcessor(
                    $this->createEventStreamProcessor($processorId, $processorClass),
                    $this->createEventStream($eventStreamClass)
                );
            }
        }

        // $this->exclusiveLock->releaseLock();
    }

    public function runEventStreamProcessor(EventStreamProcessor $processor, EventStream $stream): void
    {
        foreach ($this->queuedEvents($processor::id(), $stream) as $event) {
            $wrapper = new EventStreamProcessorWrapper($processor);
            $wrapper->process($event);
            $this->streamPositionWriter->writePosition($processor::id(), $event->id());
        }
    }

    private function queuedEvents(UUID $handlerId, EventStream $eventStream): Events
    {
        $position = $this->streamPositionReader->readPosition($handlerId);

        if ($this->limit !== null) {
            $eventStream->limitNextQuery($this->limit);
        }

        return $eventStream->queued($position, null);
    }

    private function createEventStreamProcessor(string $id, string $class): EventStreamProcessor
    {
        return $this->eventStreamProcessorFactory->createEventStreamProcessor(
            UUID::from($id),
            $class
        );
    }

    private function createEventStream(string $class): EventStream
    {
        return $this->eventStreamFactory->createEventStream($class);
    }
}

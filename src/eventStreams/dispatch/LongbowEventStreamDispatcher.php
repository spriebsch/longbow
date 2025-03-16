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

use spriebsch\diContainer\Container;
use spriebsch\eventstore\Events;
use spriebsch\eventstore\EventStream;
use spriebsch\longbow\StreamPosition;
use spriebsch\uuid\UUID;

final class LongbowEventStreamDispatcher implements EventStreamDispatcher
{
    private ?int $limit;

    public function __construct(
        private readonly EventStreamProcessorMap $streamProcessorMap,
        private readonly StreamPosition          $streamPosition,
        private readonly Container               $container,
    ) {}

    public function run(?int $limit = null): void
    {
        $this->limit = $limit;

        foreach ($this->streamProcessorMap->streams() as $eventStreamClass => $processors) {
             foreach ($processors as $processorId => $processorClass) {
                $this->runEventStreamProcessor(
                    $this->createEventStreamProcessor($processorId, $processorClass),
                    $this->createEventStream($eventStreamClass),
                );
            }
        }
    }

    public function runEventStreamProcessor(EventStreamProcessor $processor, EventStream $stream): void
    {
        foreach ($this->queuedEvents($processor::id(), $stream) as $event) {
            $wrapper = new EventStreamProcessorWrapper($processor);
            $wrapper->process($event);
            $this->streamPosition->writePositionAndReleaseLock($processor::id(), $event->id());
        }
    }

    private function queuedEvents(UUID $handlerId, EventStream $eventStream): Events
    {
        $position = $this->streamPosition->readPositionAndLock($handlerId);

        if ($this->limit !== null) {
            $eventStream->limitNextQuery($this->limit);
        }

        return $eventStream->queued($position, null);
    }

    private function createEventStreamProcessor(string $id, string $class): EventStreamProcessor
    {
        return $this->container->get($class, UUID::from($id));
    }

    private function createEventStream(string $class): EventStream
    {
        return $this->container->get($class);
    }
}

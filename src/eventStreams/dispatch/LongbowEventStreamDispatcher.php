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
use spriebsch\eventstore\EventStream;
use spriebsch\longbow\StreamPosition;
use Throwable;

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

            /** @var EventStream $stream */
            $stream = $this->container->get($eventStreamClass);

            $this->processStream($stream, $processors);
        }
    }

    private function processStream(EventStream $stream, array $processors): void
    {
        foreach ($processors as $processorId => $processorClass) {
            /** @var EventStreamProcessor $processor */
            $processor = $this->container->get($processorClass);

            if ($processorId !== $processor::id()->asString()) {
                throw new EventStreamProcessorIDMismatch($processorClass, $processorId);
            }

            $this->runEventStreamProcessor($processor, $stream);
        }
    }

    public function runEventStreamProcessor(EventStreamProcessor $processor, EventStream $stream): void
    {
        $this->streamPosition->acquireLock($processor::id());
        $position = $this->streamPosition->readPosition($processor::id());

        if ($this->limit !== null) {
            $stream->limitNextQuery($this->limit);
        }

        try {
            foreach ($stream->queued($position) as $event) {
                new EventStreamProcessorWrapper($processor)->process($event);
                $this->streamPosition->writePosition($processor::id(), $event->id());
            }
        }

        catch (Throwable $exception) {
            // @todo log this exception somewhere
        }

        $this->streamPosition->releaseLock($processor::id());
    }
}

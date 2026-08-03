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
use spriebsch\longbow\StreamPosition;
use Throwable;

final class LongbowEventStreamDispatcher implements EventStreamDispatcher
{
    private ?int $limit;
    private array $exceptions = [];

    public function __construct(
        private readonly EventStreamProcessorMap      $streamProcessorMap,
        private readonly StreamPosition               $streamPosition,
        private readonly EventStreamProcessorFailures $processorFailures,
        private readonly Container                    $container,
    ) {}

    public function run(?int $limit = null): array
    {
        $this->limit = $limit;
        $this->exceptions = [];

        foreach ($this->streamProcessorMap->streams() as $eventStreamClass => $processors) {

            /** @var EventStream $stream */
            $stream = $this->container->get($eventStreamClass);

            $this->processStream($stream, $processors);
        }

        return $this->exceptions;
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
        $eventId = null;

        try {
            $position = $this->streamPosition->readPosition($processor::id());
            if ($this->limit !== null) {
                $stream->withLimit($this->limit);
            }
            $events = $stream->eventsAfter($position);

            foreach ($events->envelopes() as $envelope) {
                $eventId = $envelope->eventId();
                new EventStreamProcessorWrapper($processor)->process($envelope->event());
                $this->streamPosition->writePosition($processor::id(), $eventId);
                $this->processorFailures->clear($processor::id());
            }
        } catch (Throwable $exception) {
            $this->processorFailures->record($processor::id(), $eventId, $exception);
            $this->exceptions[] = $exception;
        } finally {
            $this->streamPosition->releaseLock($processor::id());
        }
    }
}

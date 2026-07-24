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

use spriebsch\filesystem\Directory;
use spriebsch\longbow\orchestration\ExportOrchestration;
use spriebsch\longbow\orchestration\LongbowPHPArraySerializer;
use spriebsch\longbow\orchestration\PHPArraySerializer;

final class LongbowOrchestrateEventStreamProcessors implements OrchestrateEventStreamProcessors, ExportOrchestration
{
    private readonly PHPArraySerializer $serializer;
    private ?string                     $eventStreamClass      = null;
    private array                       $eventStreamProcessors = [];

    public function __construct(?PHPArraySerializer $serializer = null)
    {
        if ($serializer === null) {
            $serializer = new LongbowPHPArraySerializer;
        }

        $this->serializer = $serializer;
    }

    public function eventStream(string $eventStreamClass): self
    {
        $this->ensureClassExists($eventStreamClass);
        $this->ensureClassImplementsEventStreamInterface($eventStreamClass);

        $this->eventStreamClass = $eventStreamClass;

        return $this;
    }

    public function isProcessedBy(string $streamProcessorClass): void
    {
        $this->ensureEventStreamHasBeenSpecifiedFor($streamProcessorClass);

        assert($this->eventStreamClass !== null);

        $this->ensureClassImplementsEventStreamProcessorInterface($streamProcessorClass);
        $this->ensureStreamProcessorIdIsUnique($streamProcessorClass::id());

        // @todo check if this is a stream processor

        $this->eventStreamProcessors[$this->eventStreamClass][$streamProcessorClass::id()->asString()] =
            $streamProcessorClass;

        $this->eventStreamClass = null;
    }

    public function exportOrchestrationTo(Directory $directory): void
    {
        $this->serializer->serialize(
            $this->eventStreamProcessors,
            $directory,
            OrchestrateEventStreamProcessors::SERIALIZATION_FILE
        );
    }

    private function ensureClassExists(string $eventStreamClass): void
    {
        if (!class_exists($eventStreamClass)) {
            throw new EventStreamClassDoesNotExistException($eventStreamClass);
        }
    }

    private function ensureClassImplementsEventStreamInterface(string $eventStreamClass): void
    {
        if (!is_subclass_of($eventStreamClass, EventStream::class)) {
            throw new OrchestrateEventStreamProcessorsException(
                sprintf('Class %s is not an event stream', $eventStreamClass),
            );
        }
    }

    private function ensureEventStreamHasBeenSpecifiedFor(string $streamProcessorClass): void
    {
        if ($this->eventStreamClass === null) {
            throw new OrchestrateEventStreamProcessorsException(
                sprintf('No event stream specified for processor %s', $streamProcessorClass),
            );
        }
    }

    private function ensureStreamProcessorIdIsUnique(EventStreamProcessorId $streamProcessorId): void
    {
        foreach ($this->eventStreamProcessors as $processors) {
            if (isset($processors[$streamProcessorId->asString()])) {
                throw new OrchestrateEventStreamProcessorsException(
                    sprintf('Processor ID %s is already configured', $streamProcessorId->asString()),
                );
            }
        }
    }

    private function ensureClassImplementsEventStreamProcessorInterface(string $streamProcessorClass): void
    {
        if (!class_exists($streamProcessorClass)) {
            throw new OrchestrateEventStreamProcessorsException(
                sprintf('Event stream processor class %s does not exist', $streamProcessorClass),
            );
        }

        if (!is_subclass_of($streamProcessorClass, EventStreamProcessor::class)) {
            throw new OrchestrateEventStreamProcessorsException(
                sprintf('Class %s is not an event stream processor', $streamProcessorClass),
            );
        }
    }
}

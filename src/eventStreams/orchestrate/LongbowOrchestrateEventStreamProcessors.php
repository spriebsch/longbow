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
use spriebsch\uuid\UUID;

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

    private function ensureClassExists(string $eventStreamClass): void {}

    private function ensureClassImplementsEventStreamInterface(string $eventStreamClass): void {}

    private function ensureEventStreamHasBeenSpecifiedFor(string $streamProcessorClass): void {}

    private function ensureStreamProcessorIdIsUnique(UUID $streamProcessorId): void {}

    private function ensureClassImplementsEventStreamProcessorInterface(string $streamProcessorClass): void {}
}

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

use spriebsch\longbow\SafeFactory;
use spriebsch\uuid\UUID;
use Throwable;

final readonly class LongbowEventStreamProcessorFactory implements EventStreamProcessorFactory
{
    public function __construct(private SafeFactory $factory) {}

    public function createEventStreamProcessor(UUID $id, string $class): EventStreamProcessor
    {
        try {
            return $this->tryToCreateEventStreamProcessor($id, $class);
        } catch (Throwable $exception) {
            throw new FailedToCreateEventStreamProcessorException($class, $exception);
        }
    }

    private function tryToCreateEventStreamProcessor(UUID $id, string $class): EventStreamProcessor
    {
        $this->ensureClassExists($class);

        $streamProcessor = $this->factory->createEventStreamProcessor($id, $class);

        $this->ensureImplementsEventStreamProcessorInterface($streamProcessor);
        assert($streamProcessor instanceof EventStreamProcessor);

        return $streamProcessor;
    }

    private function ensureClassExists(string $class): void
    {
        if (!class_exists($class)) {
            throw new EventStreamProcessorDoesNotExistException($class);
        }
    }

    private function ensureImplementsEventStreamProcessorInterface(object $streamProcessor): void
    {
        if (
            !in_array(
                EventStreamProcessor::class,
                class_implements($streamProcessor),
                true
            )
        ) {
            throw new ClassDoesNotImplementEventStreamProcessorInterfaceException($streamProcessor::class);
        }
    }
}

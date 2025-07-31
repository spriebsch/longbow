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
use spriebsch\eventstore\Event;
use spriebsch\longbow\Longbow;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(LongbowEventStreamDispatcher::class)]
class LongbowEventStreamDispatcherTest extends TestCase
{
    public function test_processes_all_events_successfully(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        Longbow::processEvents();

        /** @var TestEventStreamProcessor $processor */
        $processor = Longbow::container()->get(TestEventStreamProcessor::class);

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $this->assertSame($expected, $processedEvents);
    }

    public function test_processes_two_events_successfully_when_third_fails(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        /** @var TestEventStreamProcessor $processor */
        $processor = Longbow::container()->get(TestEventStreamProcessor::class);
        $processor->failOn(3);

        try {
            Longbow::processEvents();
        } catch (RuntimeException) {
        }

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $expected = array_slice($expected, 0, -1);

        $this->assertSame($expected, $processedEvents);
    }

    public function test_stream_position_is_written_correctly_when_processor_fails_on_second_run(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        /** @var TestEventStreamProcessor $processor */
        $processor = Longbow::container()->get(TestEventStreamProcessor::class);
        $processor->failOn(2);

        $streamPosition = Longbow::container()->get(\spriebsch\longbow\StreamPosition::class);
        $processorId = TestEventStreamProcessor::id();

        // Reset position to start from beginning
        $streamPosition->resetPosition($processorId);

        try {
            Longbow::processEvents();
        } catch (RuntimeException) {
        }

        // Verify that only the first event was processed
        $processedEvents = $processor->getProcessedEvents();
        $this->assertCount(1, $processedEvents);

        // Verify that the position was written for the first event
        $currentPosition = $streamPosition->readPosition($processorId);
        $this->assertNotNull($currentPosition);
        $this->assertSame($fixture->events[0]->id()->asString(), $currentPosition->asString());

        // Run again to verify it continues from the correct position
        $processor->failOn(999); // Don't fail this time (use high number)
        Longbow::processEvents();

        // Should now have processed all remaining events
        $processedEvents = $processor->getProcessedEvents();
        $this->assertCount(3, $processedEvents);

        // Final position should be the last event
        $finalPosition = $streamPosition->readPosition($processorId);
        $this->assertSame($fixture->events[2]->id()->asString(), $finalPosition->asString());
    }
}

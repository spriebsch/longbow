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
}

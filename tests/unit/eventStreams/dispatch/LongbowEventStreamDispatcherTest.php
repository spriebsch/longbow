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
use spriebsch\longbow\LongbowContainer;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(LongbowEventStreamDispatcher::class)]
class LongbowEventStreamDispatcherTest extends TestCase
{
    public function test_processes_events(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        /** @var TestEventStreamProcessor $processor */
        $processor = $fixture->container->get(TestEventStreamProcessor::class);

        $fixture->dispatcher->run();

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $this->assertSame($expected, $processedEvents);
    }

    public function test_fail(): void
    {
        $fixture = new EventStreamDispatcherTestFixture;

        $toEventIds = fn(Event $event) => $event->id()->asString();
        $expected = array_map($toEventIds, $fixture->events);

        /** @var TestEventStreamProcessor $processor */
        $processor = $fixture->container->get(TestEventStreamProcessor::class);
        $processor->failOn(3);

        try {
            $fixture->dispatcher->run();
        } catch (RuntimeException $exception) {
        }

        $processedEvents = $processor->getProcessedEvents();
        $processedEvents = array_map($toEventIds, $processedEvents);

        $expected = array_slice($expected, 0, -1);

        $this->assertSame($expected, $processedEvents);
    }
}

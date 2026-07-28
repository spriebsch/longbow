<?php declare(strict_types=1);

namespace spriebsch\longbow\eventStreams;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\sequora\EventQuery;
use spriebsch\sequora\EventReader;
use spriebsch\sequora\Events;
use spriebsch\uuid\UUIDv4;

#[CoversClass(EventStream::class)]
final class EventStreamTest extends TestCase
{
    public function test_queries_configured_topics(): void
    {
        $events = Events::from();
        $reader = $this->createStub(EventReader::class);
        $reader->method('query')->willReturnCallback(
            function (EventQuery $query) use ($events): Events {
                $this->assertSame(
                    'spriebsch.longbow.tests.stream',
                    $query->criteria()->topics()[0]->asString(),
                );

                return $events;
            },
        );

        $this->stream($reader)->eventsAfter();
    }

    public function test_queries_after_position(): void
    {
        $position = EventId::generate();
        $reader = $this->createStub(EventReader::class);
        $reader->method('query')->willReturnCallback(
            function (EventQuery $query) use ($position): Events {
                $this->assertTrue($position->equals($query->criteria()->afterEventId()));

                return Events::from();
            },
        );

        $this->stream($reader)->eventsAfter($position);
    }

    public function test_limits_query(): void
    {
        $reader = $this->createStub(EventReader::class);
        $reader->method('query')->willReturnCallback(
            function (EventQuery $query): Events {
                $this->assertSame(10, $query->criteria()->limit());

                return Events::from();
            },
        );

        $stream = $this->stream($reader);
        $stream->withLimit(10);

        $stream->eventsAfter();
    }

    public function test_queries_all_events(): void
    {
        $events = Events::from();
        $reader = $this->createStub(EventReader::class);
        $reader->method('query')->willReturn($events);

        $this->assertSame($events, $this->stream($reader)->allEvents());
    }

    public function test_queries_by_correlation_id(): void
    {
        $correlationId = UUIDv4::generate();
        $reader = $this->createStub(EventReader::class);
        $reader->method('query')->willReturnCallback(
            function (EventQuery $query) use ($correlationId): Events {
                $this->assertSame(
                    $correlationId->asString(),
                    $query->criteria()->correlationIds()[0]->asString(),
                );

                return Events::from();
            },
        );

        $stream = $this->stream($reader);
        $stream->withCorrelationId($correlationId);

        $stream->eventsAfter();
    }

    private function stream(EventReader $reader): EventStream
    {
        return new class($reader) extends EventStream {
            protected function topics(): array
            {
                return [Topic::fromString('spriebsch.longbow.tests.stream')];
            }
        };
    }
}

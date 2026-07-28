<?php declare(strict_types=1);

namespace spriebsch\longbow\eventStreams;

use spriebsch\DomainEvent\CorrelationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\sequora\EventQuery;
use spriebsch\sequora\EventReader;
use spriebsch\sequora\Events;
use spriebsch\uuid\UUID;

abstract class EventStream
{
    private int $limit;
    private CorrelationId $correlationId;

    final public function __construct(private readonly EventReader $eventReader) {}

    final public function withLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    final public function withCorrelationId(UUID $correlationId): void
    {
        $this->correlationId = CorrelationId::fromUUID($correlationId);
    }

    final public function allEvents(): Events
    {
        return $this->eventsAfter(null);
    }

    final public function eventsAfter(?EventId $position = null): Events
    {
        $query = EventQuery::from()->withTopics(...$this->topics());

        if ($position !== null) {
            $query = $query->after($position);
        }

        if (isset($this->correlationId)) {
            $query = $query->withCorrelationId($this->correlationId);
        }

        if (isset($this->limit)) {
            $query = $query->limit($this->limit);
        }

        $events = $this->eventReader->query($query);

        unset($this->correlationId);
        unset($this->limit);

        return $events;
    }

    /**
     * @return list<Topic>
     */
    abstract protected function topics(): array;
}

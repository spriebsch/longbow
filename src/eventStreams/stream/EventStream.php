<?php declare(strict_types=1);

namespace spriebsch\longbow\eventStreams;

use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\sequora\EventQuery;
use spriebsch\sequora\EventReader;
use spriebsch\sequora\Events;

abstract readonly class EventStream
{
    final public function __construct(private EventReader $eventReader)
    {
    }

    final public function eventsAfter(?EventId $position = null, ?int $limit = null): Events
    {
        $query = EventQuery::from()->withTopics(...$this->topics());

        if ($position !== null) {
            $query = $query->after($position);
        }

        if ($limit !== null) {
            $query = $query->limit($limit);
        }

        return $this->eventReader->query($query);
    }

    /**
     * @return list<Topic>
     */
    abstract protected function topics(): array;
}

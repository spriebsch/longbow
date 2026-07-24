<?php declare(strict_types=1);

namespace spriebsch\longbow\tests;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;

#[MapToTopic('spriebsch.longbow.tests.test-event')]
final readonly class TestEvent implements DomainEvent
{
    public function __construct(private ?TestCorrelationId $correlationId = null)
    {
    }

    public static function create(): self
    {
        return new self(TestCorrelationId::generate());
    }

    #[UseAsCorrelationId]
    public function correlationId(): TestCorrelationId
    {
        return $this->correlationId ?? TestCorrelationId::generate();
    }
}

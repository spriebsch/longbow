<?php declare(strict_types=1);

namespace spriebsch\longbow\example;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;

#[MapToTopic('spriebsch.longbow.example-application.some-event')]
final readonly class SomeEvent implements DomainEvent
{
    public static function from(SomeId $someId, string $payload): self
    {
        return new self($someId, $payload);
    }

    private function __construct(
        private SomeId $someId,
        private string $payload,
    ) {
    }

    #[UseAsCorrelationId]
    public function someId(): SomeId
    {
        return $this->someId;
    }

    public function payload(): string
    {
        return $this->payload;
    }
}

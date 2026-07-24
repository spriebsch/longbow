<?php declare(strict_types=1);

namespace spriebsch\longbow;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;
use spriebsch\longbow\tests\TestCorrelationId;

#[MapToTopic('spriebsch.longbow.tests.dispatch-test-event')]
final readonly class DispatchTestEvent implements DomainEvent
{
    public static function from(TestCorrelationId $id, string $payload): self
    {
        return new self($id, $payload);
    }

    private function __construct(
        private TestCorrelationId $testCorrelationId,
        private string $payload,
    ) {
    }

    #[UseAsCorrelationId]
    public function testCorrelationId(): TestCorrelationId
    {
        return $this->testCorrelationId;
    }

    public function payload(): string
    {
        return $this->payload;
    }
}

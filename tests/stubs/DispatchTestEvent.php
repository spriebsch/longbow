<?php declare(strict_types=1);

namespace spriebsch\longbow;

use spriebsch\eventstore\CorrelationId;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventId;
use spriebsch\eventstore\EventTrait;
use spriebsch\eventstore\Json;
use spriebsch\eventstore\SerializableEventTrait;
use spriebsch\longbow\tests\TestCorrelationId;
use spriebsch\timestamp\Timestamp;

final readonly class DispatchTestEvent implements Event
{
    use SerializableEventTrait;
    use EventTrait;

    private string $payload;

    private function __construct(EventId $id, CorrelationId $correlationId, Timestamp $timestamp, string $payload)
    {
        $this->id = $id;
        $this->correlationId = $correlationId;
        $this->timestamp = $timestamp;
        $this->payload = $payload;
    }

    public static function fromJson(Json $json): self
    {
        return new self(
            EventId::from($json->get('id')),
            TestCorrelationId::from($json->get('correlationId')),
            Timestamp::from($json->get('timestamp')),
            $json->get('payload')
        );
    }

    public static function from(TestCorrelationId $id, string $payload): self
    {
        return new self(EventId::generate(), $id, Timestamp::generate(), $payload);
    }

    public static function topic(): string
    {
        return 'the-topic';
    }

    public function serialize(): array
    {
        return ['payload' => $this->payload];
    }

    public function testCorrelationId(): TestCorrelationId
    {
        return $this->correlationId;
    }

    public function payload(): string
    {
        return $this->payload;
    }
}

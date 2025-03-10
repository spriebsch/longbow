<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\example;

use spriebsch\eventstore\CorrelationId;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventId;
use spriebsch\eventstore\EventTrait;
use spriebsch\eventstore\Json;
use spriebsch\eventstore\SerializableEventTrait;
use spriebsch\timestamp\Timestamp;

final class SomeEvent implements Event
{
    use SerializableEventTrait;
    use EventTrait;

    private readonly string $payload;

    public static function from(SomeId $someId, string $payload): self
    {
        return new self(EventId::generate(), $someId, Timestamp::generate(), $payload);
    }

    public static function fromJson(Json $json): self
    {
        return new self(
            EventId::from($json->get('id')),
            SomeId::from($json->get('correlationId')),
            Timestamp::from($json->get('timestamp')),
            $json->get('payload')
        );
    }

    private function __construct(
        EventId       $id,
        CorrelationId $correlationId,
        Timestamp     $timestamp,
        string        $payload
    )
    {
        $this->id = $id;
        $this->correlationId = $correlationId;
        $this->timestamp = $timestamp;
        $this->payload = $payload;
    }

    public function serialize(): array
    {
        return [
            'payload' => $this->payload
        ];
    }

    public static function topic(): string
    {
        return 'spriebsch.longbow.exampleApplication';
    }

    public function someId(): SomeId
    {
        return $this->correlationId;
    }

    public function payload(): string
    {
        return $this->payload;
    }
}
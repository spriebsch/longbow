<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\tests;

use spriebsch\eventstore\CorrelationId;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventId;
use spriebsch\eventstore\Json;
use spriebsch\timestamp\Timestamp;

class TestEvent implements Event
{
    public readonly EventId $id;

    public static function fromJson(Json $json): Event
    {
        return new self;
    }

    public function __construct()
    {
        $this->id = EventId::generate();
    }

    public static function topic(): string
    {
        return 'the-topic';
    }

    public function id(): EventId
    {
        return $this->id;
    }

    public function timestamp(): Timestamp
    {
        return Timestamp::generate();
    }

    public function correlationId(): CorrelationId
    {
        return TestCorrelationId::generate();
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}

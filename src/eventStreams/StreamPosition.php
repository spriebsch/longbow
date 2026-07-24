<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow;

use spriebsch\DomainEvent\EventId;
use spriebsch\longbow\eventStreams\EventStreamProcessorId;

interface StreamPosition
{
    public function readPosition(EventStreamProcessorId $handlerId): ?EventId;

    public function acquireLock(EventStreamProcessorId $handlerId): void;

    public function writePosition(EventStreamProcessorId $handlerId, EventId $eventId): void;

    public function releaseLock(EventStreamProcessorId $handlerId): void;

    public function resetPosition(EventStreamProcessorId $handlerId): void;
}

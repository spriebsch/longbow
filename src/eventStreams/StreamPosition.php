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

use spriebsch\eventstore\EventId;
use spriebsch\uuid\UUID;

interface StreamPosition
{
    public function readPosition(UUID $handlerId): ?EventId;

    public function acquireLock(UUID $handlerId): void;

    public function writePosition(UUID $handlerId, EventId $eventId): void;

    public function releaseLock(UUID $handlerId): void;
}

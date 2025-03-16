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
    public function readPositionAndLock(UUID $handlerId): ?EventId;

    public function writePositionAndReleaseLock(UUID $handlerId, EventId $eventId): void;
}

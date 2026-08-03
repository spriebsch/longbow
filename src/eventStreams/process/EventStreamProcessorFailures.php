<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\eventStreams;

use spriebsch\DomainEvent\EventId;
use Throwable;

interface EventStreamProcessorFailures
{
    public function failureOf(EventStreamProcessorId $processorId): ?EventStreamProcessorFailure;

    public function record(
        EventStreamProcessorId $processorId,
        ?EventId               $eventId,
        Throwable              $exception,
    ): void;

    public function clear(EventStreamProcessorId $processorId): void;
}

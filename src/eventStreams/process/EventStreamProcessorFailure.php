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

final readonly class EventStreamProcessorFailure
{
    public function __construct(
        private ?EventId $eventId,
        private string   $failedAt,
        private string   $exceptionClass,
        private string   $exceptionMessage,
    ) {}

    public function eventId(): ?EventId
    {
        return $this->eventId;
    }

    public function failedAt(): string
    {
        return $this->failedAt;
    }

    public function exceptionClass(): string
    {
        return $this->exceptionClass;
    }

    public function exceptionMessage(): string
    {
        return $this->exceptionMessage;
    }
}

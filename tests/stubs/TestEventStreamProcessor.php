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

use spriebsch\longbow\DispatchTestEvent;
use spriebsch\longbow\eventStreams\EventStreamProcessor;
use spriebsch\uuid\UUID;

class TestEventStreamProcessor implements EventStreamProcessor
{
    private array $processedEvents;

    public static function id(): UUID
    {
        return UUID::from('498fe307-073b-4dc1-8dd6-989466a98f53');
    }

    public function onDispatchTestEvent(DispatchTestEvent $event): void
    {
        $this->processedEvents[] = $event;
    }

    public function getProcessedEvents(): array
    {
        return $this->processedEvents;
    }
}

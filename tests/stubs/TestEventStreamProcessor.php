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

use RuntimeException;
use spriebsch\longbow\DispatchTestEvent;
use spriebsch\longbow\eventStreams\EventStreamProcessor;
use spriebsch\uuid\UUID;

class TestEventStreamProcessor implements EventStreamProcessor
{
    private array $processedEvents;
    private ?int $failOn = null;
    private int $runs = 0;

    public static function id(): UUID
    {
        return UUID::from('498fe307-073b-4dc1-8dd6-989466a98f53');
    }

    public function onDispatchTestEvent(DispatchTestEvent $event): void
    {
        var_dump('DISPATCH');

        if ($this->failOn) {
            if ($this->runs >= $this->failOn - 1) {
                throw new RuntimeException(sprintf('I was told to fail on run %s', $this->failOn));
            }
        }

        $this->processedEvents[] = $event;
        $this->runs ++;
    }

    public function failOn(int $run): void
    {
        $this->runs = 0;
        $this->failOn = $run;
    }

    public function getProcessedEvents(): array
    {
        return $this->processedEvents;
    }
}

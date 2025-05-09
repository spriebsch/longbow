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

use spriebsch\longbow\eventStreams\EventStreamProcessor;
use spriebsch\uuid\UUID;

final class SomeEventStreamProcessor implements EventStreamProcessor
{
    public function __construct(private readonly EventStreamProcessorSideEffect $sideEffect) {}

    public static function id(): UUID
    {
        return UUID::from('11111111-47d5-44f6-a699-e382937ffbd9');
    }

    public function onSomeEvent(SomeEvent $event): void
    {
        $this->sideEffect->setPayload($event->payload());
    }
}

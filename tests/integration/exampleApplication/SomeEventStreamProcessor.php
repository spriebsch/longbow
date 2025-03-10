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
    public function __construct(private readonly Something2 $something2) {}

    public static function id(): UUID
    {
        return UUID::from('8e6c3643-47d5-44f6-a699-e382937ffbd9');
    }

    public function onSomeEvent(SomeEvent $event): void
    {
        $this->something2->setPayload($event->payload());
    }
}
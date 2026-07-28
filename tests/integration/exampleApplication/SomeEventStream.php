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

use spriebsch\longbow\eventStreams\EventStream;
use spriebsch\DomainEvent\Topic;

final class SomeEventStream extends EventStream
{
    protected function topics(): array
    {
        return [Topic::fromString('spriebsch.longbow.example-application.some-event')];
    }
}

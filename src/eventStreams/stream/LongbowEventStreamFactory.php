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

use spriebsch\eventstore\EventStream;
use spriebsch\longbow\SafeFactory;

final readonly class LongbowEventStreamFactory implements EventStreamFactory
{
    public function __construct(private SafeFactory $factory) {}

    public function createEventStream(string $eventStreamClass): EventStream
    {
        if (!class_exists($eventStreamClass)) {
            throw new EventStreamClassDoesNotExistException($eventStreamClass);
        }

        $eventStream = $this->factory->create($eventStreamClass);

        assert($eventStream instanceof EventStream);

        return $eventStream;
    }
}

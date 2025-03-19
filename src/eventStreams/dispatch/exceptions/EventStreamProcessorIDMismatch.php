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

use spriebsch\longbow\Exception;

final class EventStreamProcessorIDMismatch extends Exception
{
    public function __construct(EventStreamProcessor $eventStreamProcessor, string $id)
    {
        parent::__construct(
            sprintf(
                'Event stream processor %s ID %s does not match %s',
                $eventStreamProcessor::class,
                $eventStreamProcessor::class::id(),
                $id
            )
        );
    }
}

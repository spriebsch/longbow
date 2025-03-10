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

use spriebsch\eventstore\Event;
use spriebsch\longbow\Exception;

final class MethodDoesNotHandleEventException extends Exception
{
    public function __construct(EventStreamProcessor $eventStreamProcessor, string $method, Event $event)
    {
        parent::__construct(
            sprintf(
                'Event stream processor method %s::%s() does not handle event %s',
                $eventStreamProcessor::class,
                $method,
                $event::class
            )
        );
    }
}
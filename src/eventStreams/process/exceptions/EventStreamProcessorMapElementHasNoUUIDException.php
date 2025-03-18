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

final class EventStreamProcessorMapElementHasNoUUIDException extends Exception
{
    public function __construct(string $stream, mixed $id, string $processor)
    {
        parent::__construct(
            sprintf(
                'Event stream processor map %s key %s of %s is no processor ID',
                $stream,
                $id,
                $processor
            )
        );
    }
}

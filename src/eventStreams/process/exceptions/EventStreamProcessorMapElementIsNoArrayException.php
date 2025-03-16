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

final class EventStreamProcessorMapElementIsNoArrayException extends Exception
{
    public function __construct(string $stream)
    {
        parent::__construct(
            sprintf(
                'Event stream processor map element %s is no array',
                $stream
            )
        );
    }
}

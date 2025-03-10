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

use spriebsch\filesystem\File;
use spriebsch\longbow\Exception;

final class EventStreamProcessorMapDoesNotReturnArrayException extends Exception
{
    public function __construct(File $map)
    {
        parent::__construct(
            sprintf(
                'Event stream processor map %s does not return array',
                $map->asString()
            )
        );
    }
}
<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\events;

use spriebsch\filesystem\File;
use spriebsch\longbow\Exception;

final class HandlerMapDoesNotReturnArrayException extends Exception
{
    public function __construct(File $eventHandlerMap)
    {
        parent::__construct(
            sprintf(
                'Event handler map %s does not return array',
                $eventHandlerMap->asString()
            )
        );
    }
}

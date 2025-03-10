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

use spriebsch\longbow\Exception;

final class HandleMethodParameterIsNotTheEventToBeHandledException extends Exception
{
    public function __construct(string $class, string $eventClass)
    {
        parent::__construct(
            sprintf(
                'Event handler method %s::handle() parameter is not %s',
                $class,
                $eventClass
            )
        );
    }
}

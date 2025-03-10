<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\commands;

use spriebsch\eventstore\Event;
use spriebsch\longbow\Exception;

final class CommandHandlerDoesNotReturnEventException extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct(
            sprintf(
                'Command handler %s does not return an instance of %s',
                $class,
                Event::class
            )
        );
    }
}

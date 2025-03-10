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

use spriebsch\longbow\Exception;

final class CommandHandlerHandleMethodDoesNotHaveExactlyOneParameterException extends Exception
{
    public function __construct(string $commandHandlerClass)
    {
        parent::__construct(
            sprintf(
                'Command handler method %s::handle() must have exactly one parameter',
                $commandHandlerClass
            )
        );
    }
}

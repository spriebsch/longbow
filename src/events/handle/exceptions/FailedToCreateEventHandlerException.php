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
use Throwable;

final class FailedToCreateEventHandlerException extends Exception
{
    public function __construct(string $class, Throwable $exception)
    {
        parent::__construct(
            sprintf(
                'Failed to create event handler %s: %s',
                $class,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception->getPrevious()
        );
    }
}

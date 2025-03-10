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
use Throwable;

final class FailedToConfigureCommandException extends Exception
{
    public function __construct(string $commandClass, Throwable $exception)
    {
        parent::__construct(
            sprintf(
                'Failed to configure command "%s": %s',
                $commandClass,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}

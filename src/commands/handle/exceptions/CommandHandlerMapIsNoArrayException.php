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

use spriebsch\filesystem\File;
use spriebsch\longbow\Exception;

final class CommandHandlerMapIsNoArrayException extends Exception
{
    public function __construct(File $classMap)
    {
        parent::__construct(
            sprintf(
                'Command handler map %s is no array',
                $classMap->asString()
            )
        );
    }
}

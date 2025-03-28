<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow;

use RuntimeException;
use spriebsch\uuid\UUID;

final class FailedToResetStreamPositionException extends RuntimeException implements LongbowException
{
    public function __construct(UUID $handler)
    {
        parent::__construct(
            sprintf(
                'Failed to reset stream position for handler "%s"',
                $handler->asString(),
            ),
        );
    }
}

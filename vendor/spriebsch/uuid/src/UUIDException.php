<?php declare(strict_types=1);

/*
 * This file is part of UUID.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\uuid;

use RuntimeException;

class UUIDException extends RuntimeException
{
    public static function malformedUUID(string $id): self
    {
        return new self(sprintf('Malformed UUID "%s"', $id));
    }
}

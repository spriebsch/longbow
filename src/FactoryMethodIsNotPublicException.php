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

use Exception;

final class FactoryMethodIsNotPublicException extends Exception
{
    public function __construct(
        object $factory,
        string $method
    )
    {
        parent::__construct(
            sprintf(
                'Factory method %s::%s is not public',
                $factory::class,
                $method
            )
        );
    }
}

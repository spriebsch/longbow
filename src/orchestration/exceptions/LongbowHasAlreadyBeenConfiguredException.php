<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\orchestration;

use spriebsch\longbow\Exception;

final class LongbowHasAlreadyBeenConfiguredException extends Exception
{
    public function __construct()
    {
        parent::__construct('Longbow has already been configured');
    }
}

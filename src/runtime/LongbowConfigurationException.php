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

final class LongbowConfigurationException extends Exception
{
    public function __construct(string $setting)
    {
        parent::__construct(
            sprintf(
                'Longbow configuration has no setting "%s"',
                $setting,
            ),
        );
    }
}

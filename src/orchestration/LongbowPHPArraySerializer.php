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

use spriebsch\filesystem\Directory;

final readonly class LongbowPHPArraySerializer implements PHPArraySerializer
{
    public function serialize(array $data, Directory $directory, string $filename): void
    {
        $directory->createFile(
            $filename,
            sprintf(
                "<?php declare(strict_types=1); \nreturn %s;\n",
                var_export($data, true)
            )
        );
    }
}

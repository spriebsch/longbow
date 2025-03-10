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

use spriebsch\longbow\tests\TestEventStream;
use spriebsch\longbow\tests\TestEventStreamProcessor;

return [
    TestEventStream::class =>
        [
            '76c88900-9f76-47fc-ad40-27758934848b' => TestEventStreamProcessor::class
        ]
];
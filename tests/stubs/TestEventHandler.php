<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\tests;

use spriebsch\longbow\events\EventHandler;

class TestEventHandler implements EventHandler
{
    private array $calls = [];

    public function handle(TestEvent $testEvent): void
    {
        $this->calls[] = $testEvent;
    }

    public function calls(): array
    {
        return $this->calls;
    }
}

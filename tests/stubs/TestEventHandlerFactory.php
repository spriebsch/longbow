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
use spriebsch\longbow\events\EventHandlerFactory;

class TestEventHandlerFactory implements EventHandlerFactory
{
    public function __construct(private readonly EventHandler $eventHandler) {}

    public function createEventHandler(string $class): EventHandler
    {
        return $this->eventHandler;
    }
}

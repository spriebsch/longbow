<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\example;

use spriebsch\longbow\events\EventHandler;

class SomeEventHandler implements EventHandler
{
    public function __construct(private readonly Something $spy) {}

    public function handle(SomeEvent $event): void
    {
        $this->spy->setPayload($event->payload());
    }
}
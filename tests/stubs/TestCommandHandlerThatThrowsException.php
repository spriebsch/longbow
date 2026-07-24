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

use RuntimeException;
use spriebsch\DomainEvent\DomainEvent;
use spriebsch\longbow\commands\CommandHandler;

final class TestCommandHandlerThatThrowsException implements CommandHandler
{
    public function handle(TestCommand $command): DomainEvent
    {
        throw new RuntimeException;
    }
}

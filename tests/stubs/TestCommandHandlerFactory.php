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

use spriebsch\longbow\commands\CommandHandler;

class TestCommandHandlerFactory
{
    public function __construct(private readonly ?CommandHandler $commandHandler) {}

    public function testCommandHandlerThatReturnsEvent(): TestCommandHandlerThatReturnsEvent
    {
        return $this->commandHandler;
    }

    public function testCommandHandlerThatThrowsException(): TestCommandHandlerThatThrowsException
    {
        return $this->commandHandler;
    }
}

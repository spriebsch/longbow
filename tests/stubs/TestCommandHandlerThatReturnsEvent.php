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

use spriebsch\eventstore\Event;
use spriebsch\longbow\commands\CommandHandler;

class TestCommandHandlerThatReturnsEvent implements CommandHandler
{
    private static Event $event;

    public static function willReturn(Event $event): void
    {
        self::$event = $event;
    }

    public function handle(TestCommand $command): Event
    {
        return self::$event;
    }
}

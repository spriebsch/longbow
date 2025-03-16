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

use spriebsch\diContainer\Container;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventFactory;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;
use spriebsch\longbow\commands\Command;
use spriebsch\longbow\orchestration\LongbowHasAlreadyBeenConfiguredException;

final class Longbow
{
    private static ?LongbowFactory $factory = null;

    public static function configure(
        Directory $orchestration,
        File      $eventMap,
        string $eventStoreDb,
        string $positionsDb,
        Container $container,
    ): void
    {
        if (self::$factory !== null) {
            throw new LongbowHasAlreadyBeenConfiguredException;
        }

        self::$factory = new LongbowFactory(
            $orchestration,
            $eventMap,
            $eventStoreDb,
            $positionsDb,
            $container,
        );
    }

    public static function reset(): void
    {
        EventFactory::reset();
        self::$factory = null;
    }

    public static function handleCommand(Command $command): Event
    {
        return self::$factory->commandDispatcher()->handle($command);
    }

    public static function processEvents(): void
    {
        self::$factory->eventStreamDispatcher()->run();
    }
}

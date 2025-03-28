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

use spriebsch\diContainer\Configuration;
use spriebsch\diContainer\Container;
use spriebsch\diContainer\DIContainer;
use spriebsch\eventstore\Event;
use spriebsch\eventstore\EventFactory;
use spriebsch\filesystem\File;
use spriebsch\longbow\commands\Command;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\orchestration\LongbowHasAlreadyBeenConfiguredException;
use spriebsch\uuid\UUID;

final class Longbow
{
    private static ?Container $container = null;

    public static function configure(
        Configuration $configuration,
        File          $eventMap,
        string        $factoryClass,
    ): void
    {
        if (self::$container !== null) {
            throw new LongbowHasAlreadyBeenConfiguredException;
        }

        EventFactory::configureWith($eventMap->require());

        self::$container = new DIContainer(
            $configuration,
            $factoryClass,
            LongbowFactory::class
        );
    }

    public static function container(): Container
    {
        if (self::$container === null) {
            throw new LongbowHasNotBeenConfiguredException;
        }

        return self::$container;
    }

    public static function reset(): void
    {
        EventFactory::reset();
        self::$container = null;
    }

    public static function handleCommand(Command $command): Event
    {
        return self::$container->get(CommandDispatcher::class)->handle($command);
    }

    public static function processEvents(): void
    {
        self::$container->get(EventStreamDispatcher::class)->run();
    }

    public function resetEventStreamProcessor(UUID $id): void
    {
        self::$container->get(StreamPosition::class)->resetPosition($id);
    }
}

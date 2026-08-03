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
use spriebsch\diContainer\DIContainer;
use spriebsch\DomainEvent\DomainEvent;
use spriebsch\longbow\commands\Command;
use spriebsch\longbow\commands\CommandDispatcher;
use spriebsch\longbow\eventStreams\EventStreamDispatcher;
use spriebsch\longbow\eventStreams\EventStreamProcessorId;
use spriebsch\longbow\eventStreams\EventStreamProcessorFailures;
use spriebsch\longbow\orchestration\LongbowHasAlreadyBeenConfiguredException;

final class Longbow
{
    private static ?Container $container = null;
    /** @var list<\Throwable> */
    private static array $exceptions = [];

    public static function configure(
        LongbowConfiguration $configuration,
        string ...$factoryClasses
    ): void
    {
        if (self::$container !== null) {
            throw new LongbowHasAlreadyBeenConfiguredException;
        }

        self::$container = new DIContainer(
            $configuration,
            ...array_merge($factoryClasses, [LongbowFactory::class])
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
        self::$container = null;
        self::$exceptions = [];
    }

    public static function handleCommand(Command $command): DomainEvent
    {
        $dispatcher = self::container()->get(CommandDispatcher::class);
        assert($dispatcher instanceof CommandDispatcher);

        return $dispatcher->handle($command);
    }

    public static function processEvents(): void
    {
        /** @var EventStreamDispatcher $dispatcher */
        $dispatcher = self::container()->get(EventStreamDispatcher::class);
        assert($dispatcher instanceof EventStreamDispatcher);
        self::$exceptions = $dispatcher->run();
    }

    public static function resetEventStreamProcessor(EventStreamProcessorId $id): void
    {
        $streamPosition = self::container()->get(StreamPosition::class);
        assert($streamPosition instanceof StreamPosition);
        $streamPosition->resetPosition($id);
    }

    public static function processorFailures(): EventStreamProcessorFailures
    {
        return self::container()->get(EventStreamProcessorFailures::class);
    }

    /** @return list<\Throwable> */
    public static function exceptions(): array
    {
        return self::$exceptions;
    }
}

<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\events;

use spriebsch\eventstore\Event;
use spriebsch\filesystem\File;

final readonly class EventHandlerMap
{
    public function __construct(private array $eventHandlerMap)
    {
        foreach ($eventHandlerMap as $eventClass => $handlers) {
            if (!is_array($handlers)) {
                throw new HandlerMapElementIsNoArrayException($eventClass);
            }
        }

    }

    public static function fromFile(File $map): self
    {
        return new self($map->require());
    }

    public static function fromArray(array $map): self
    {
        return new self($map);
    }

    public function handlerClassesFor(Event $event): array
    {
        return $this->eventHandlerMap[$event::class] ?? [];
    }
}

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

final readonly class LongbowEventHandlerMap implements EventHandlerMap
{
    private array $eventHandlers ;

    public function __construct(private File $eventHandlerMap) {}

    public function handlerClassesFor(Event $event): array
    {
        $this->load();

        $element = $this->eventHandlers[$event::class] ?? [];

        $this->ensureHandlerMapElementIsArray(
            $element,
            $this->eventHandlerMap,
            $event
        );

        return $element;
    }

    private function load(): void
    {
        if (isset($this->eventHandlers)) {
            return;
        }

        $map = $this->eventHandlerMap->require();

        $this->ensureMapIsArray($this->eventHandlerMap, $map);

        $this->eventHandlers = $map;
    }

    private function ensureHandlerMapElementIsArray(
        mixed $element,
        File  $eventHandlerMap,
        Event $event
    )
    {
        if (!is_array($element)) {
            throw new HandlerMapElementIsNoArrayException($eventHandlerMap, $event);
        }
    }

    private function ensureMapIsArray(File $eventHandlerMap, mixed $map): void
    {
        if (!is_array($map)) {
            throw new HandlerMapDoesNotReturnArrayException($this->eventHandlerMap);
        }
    }
}
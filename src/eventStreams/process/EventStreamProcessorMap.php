<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\eventStreams;

use spriebsch\filesystem\File;

final readonly class EventStreamProcessorMap
{
    public function __construct(private array $eventStreamProcessorMap)
    {
        foreach ($eventStreamProcessorMap as $stream => $processors) {
            if (!is_array($processors)) {
                throw new EventStreamProcessorMapElementIsNoArrayException($stream);
            }

            foreach ($processors as $id => $processor) {
                if (!is_string($id)) {
                    throw new EventStreamProcessorMapElementHasNoUUIDException($stream, $id, $processor);
                }
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

    public function streams(): array
    {
        return $this->eventStreamProcessorMap;
    }
}

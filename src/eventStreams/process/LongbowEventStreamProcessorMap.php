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

final readonly class LongbowEventStreamProcessorMap implements EventStreamProcessorMap
{
    private array $map;

    public function __construct(private File $eventStreamProcessorMap) {}

    public function streams(): array
    {
        $this->loadMap();

        return $this->map;
    }

    private function loadMap(): void
    {
        if (isset($this->map)) {
            return;
        }

        $result = $this->eventStreamProcessorMap->require();

        if (!is_array($result)) {
            throw new EventStreamProcessorMapDoesNotReturnArrayException
            (
                $this->eventStreamProcessorMap
            );
        }

        foreach ($result as $stream => $processors) {
            if (!is_array($processors)) {
                throw new EventStreamProcessorMapElementIsNoArrayException
                (
                    $this->eventStreamProcessorMap,
                    $stream
                );
            }
        }

        $this->map = $result;
    }
}
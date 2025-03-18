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
use spriebsch\eventstore\EventReader;
use spriebsch\eventstore\EventWriter;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;

final readonly class LongbowContainer implements Container
{
    private LongbowFactory $longbowFactory;
    private Container $wrapper;

    public function __construct(
        private Directory $orchestrationDirectory,
        private File      $eventMap,
        private string    $eventStoreDb,
        private string    $positionsDb,
        private Container $container,
    )
    {
        $this->longbowFactory = new LongbowFactory(
            $this->orchestrationDirectory,
            $this->eventMap,
            $this->eventStoreDb,
            $this->positionsDb,
            $this->container,
        );
    }

    public function get(string $type, ...$parameters): object
    {
        if (isset($this->wrapper)) {
            return $this->wrapper->get($type, ...$parameters);
        }

        return $this->doGet($type, ...$parameters);
    }

    public function delegateGet(Container $wrapper, string $type, ...$parameters): object
    {
        $this->wrapper = $wrapper;

        return $this->doGet($type, ...$parameters);
    }

    private function doGet(string $type, mixed ...$parameters)
    {
        return match ($type) {
            EventReader::class => $this->longbowFactory->eventReader(),
            EventWriter::class => $this->longbowFactory->eventWriter(),
            SqliteStreamPosition::class => $this->longbowFactory->streamPosition(),
            default            => $this->container->delegateGet($this, $type, ...$parameters)
        };
    }
}

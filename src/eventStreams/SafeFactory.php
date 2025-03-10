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

use ReflectionClass;
use ReflectionMethod;
use spriebsch\eventstore\EventWriter;
use spriebsch\sqlite\Connection;
use spriebsch\uuid\UUID;

final readonly class SafeFactory
{
    public function __construct(private object $factory) {}

    public function streamPositionConnection(): Connection
    {
        return $this->factory->streamPositionConnection();
    }

    public function eventWriter(): EventWriter
    {
        // @check if method exists

        return $this->factory->eventWriter();
    }

    public function createEventStreamProcessor(UUID $id, string $class): object
    {
        $method = lcfirst((new ReflectionClass($class))->getShortName());

        $this->ensureHasMethod($this->factory, $method);
        $this->ensureMethodIsPublic($this->factory, $method);

        // @todo check return type
        // @todo add checks: one UUID parameter

        return $this->factory->{$method}($id);
    }

    public function create(string $class): object
    {
        $method = lcfirst((new ReflectionClass($class))->getShortName());

        $this->ensureHasMethod($this->factory, $method);
        $this->ensureMethodIsPublic($this->factory, $method);

        // @todo check return type
        // @todo add checks: no parameters

        return $this->factory->{$method}();
    }

    private function ensureHasMethod(object $factory, string $method): void
    {
        if (!method_exists($factory, $method)) {
            throw new FactoryHasNoSuchMethodException(
                $factory,
                $method
            );
        }
    }

    private function ensureMethodIsPublic(object $factory, string $method): void
    {
        if (!$this->reflectMethod($factory, $method)->isPublic()) {
            throw new FactoryMethodIsNotPublicException(
                $factory,
                $method
            );
        }
    }

    private function reflectMethod($factory, $method): ReflectionMethod
    {
        return (new ReflectionClass($factory))->getMethod($method);
    }
}
<?php declare(strict_types=1);

namespace spriebsch\longbow\tests;

use spriebsch\DomainEvent\Topic;
use spriebsch\longbow\eventStreams\EventStream;

final class TestEventStream extends EventStream
{
    protected function topics(): array
    {
        return [Topic::fromString('spriebsch.longbow.tests.test-event')];
    }
}

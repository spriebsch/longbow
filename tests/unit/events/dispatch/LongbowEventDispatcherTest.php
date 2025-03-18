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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use spriebsch\diContainer\DIContainer;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\example\ApplicationFactory;
use spriebsch\longbow\example\LongbowConfiguration;
use spriebsch\longbow\tests\TestEvent;
use spriebsch\longbow\tests\TestEventHandler;

#[CoversClass(LongbowEventDispatcher::class)]
#[CoversClass(EventHandlerMap::class)]
class LongbowEventDispatcherTest extends TestCase
{
    #[Group('feature')]
    public function test_dispatches_to_EventHandler(): void
    {
        $configuration = LongbowConfiguration::fromArray(
            [
                'orchestrationDirectory' => Filesystem::from(__DIR__ . '/../../../../data'),
                'eventStore' => ':memory:',
                'longbowDatabase' => ':memory:',
            ],
        );

        $container = new DiContainer($configuration, ApplicationFactory::class);

        $eventHandlerMap = EventHandlerMap::fromArray([TestEvent::class => [TestEventHandler::class]]);

        $dispatcher = new LongbowEventDispatcher($eventHandlerMap, $container);

        $dispatcher->dispatch(new TestEvent);

        $handler = $container->get(TestEventHandler::class);

        $this->assertCount(1, $handler->calls());
    }
}

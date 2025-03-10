<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\File;
use spriebsch\filesystem\Filesystem;
use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;

#[CoversClass(LongbowCommandHandlerMap::class)]
#[CoversClass(CommandHasNoHandlerException::class)]
#[CoversClass(CommandHandlerMapIsNoArrayException::class)]
class LongbowCommandHandlerMapTest extends TestCase
{
    #[Group('feature')]
    public function test_provides_CommandHandler_class_for_given_Command(): void
    {
        $map = new LongbowCommandHandlerMap(
            Filesystem::from(__DIR__ . '/../../../fixtures/CommandMap.php')
        );

        $handlerClass = $map->handlerClassFor(new TestCommand);

        $this->assertSame(TestCommandHandlerThatReturnsEvent::class, $handlerClass);
    }

    #[Group('feature')]
    public function test_loads_map_only_once(): void
    {
        $file = $this->createMock(File::class);
        $file
            ->expects($this->once())
            ->method('require')
            ->willReturn(require __DIR__ . '/../../../fixtures/CommandMap.php');

        $map = new LongbowCommandHandlerMap($file);

        $map->handlerClassFor(new TestCommand);
        $map->handlerClassFor(new TestCommand);
    }

    #[Group('exception')]
    public function test_CommandHandler_must_be_configured_for_given_Command(): void
    {
        $map = new LongbowCommandHandlerMap(
            Filesystem::from(__DIR__ . '/../../../fixtures/EmptyMap.php')
        );

        $this->expectException(CommandHasNoHandlerException::class);

        $map->handlerClassFor(new TestCommand);
    }

    #[Group('exception')]
    public function test_stored_map_must_be_array(): void
    {
        $map = new LongbowCommandHandlerMap(
            Filesystem::from(__DIR__ . '/../../../fixtures/InvalidMap.php')
        );

        $this->expectException(CommandHandlerMapIsNoArrayException::class);

        $map->handlerClassFor(new TestCommand);
    }
}

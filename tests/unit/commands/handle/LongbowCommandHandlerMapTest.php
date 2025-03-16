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

#[CoversClass(CommandHandlerMap::class)]
#[CoversClass(CommandHasNoHandlerException::class)]
class LongbowCommandHandlerMapTest extends TestCase
{
    #[Group('feature')]
    public function test_provides_CommandHandler_class_for_given_Command(): void
    {
        $map = CommandHandlerMap::fromFile(
            Filesystem::from(__DIR__ . '/../../../fixtures/CommandMap.php')
        );

        $handlerClass = $map->handlerClassFor(new TestCommand);

        $this->assertSame(TestCommandHandlerThatReturnsEvent::class, $handlerClass);
    }

    public function test_provides_CommandHandler_class_for_given_Command_array(): void
    {
        $map = CommandHandlerMap::fromArray(
            [TestCommand::class => TestCommandHandlerThatReturnsEvent::class]
        );

        $handlerClass = $map->handlerClassFor(new TestCommand);

        $this->assertSame(TestCommandHandlerThatReturnsEvent::class, $handlerClass);
    }

    #[Group('exception')]
    public function test_CommandHandler_must_be_configured_for_given_Command(): void
    {
        $map = CommandHandlerMap::fromArray([]);

        $this->expectException(CommandHasNoHandlerException::class);

        $map->handlerClassFor(new TestCommand);
    }
}

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

use spriebsch\filesystem\File;

final readonly class LongbowCommandHandlerMap implements CommandHandlerMap
{
    private array $commandHandlers;

    public function __construct(private File $commandHandlerMap) {}

    public function handlerClassFor(Command $command): string
    {
        $this->load();

        if (!isset($this->commandHandlers[$command::class])) {
            throw new CommandHasNoHandlerException($command);
        }

        return $this->commandHandlers[$command::class];
    }

    private function load(): void
    {
        if (isset($this->commandHandlers)) {
            return;
        }

        $map = $this->commandHandlerMap->require();

        $this->ensureMapIsArray($map);

        $this->commandHandlers = $map;
    }

    private function ensureMapIsArray(mixed $map): void
    {
        if (!is_array($map)) {
            throw new CommandHandlerMapIsNoArrayException($this->commandHandlerMap);
        }
    }
}
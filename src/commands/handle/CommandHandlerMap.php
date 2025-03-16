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

final readonly class CommandHandlerMap
{
    public function __construct(private array $commandHandlers) {}

    public static function fromFile(File $map): self
    {
        return new self($map->require());
    }

    public static function fromArray(array $map): self
    {
        return new self($map);
    }

    public function handlerClassFor(Command $command): string
    {
        if (!isset($this->commandHandlers[$command::class])) {
            throw new CommandHasNoHandlerException($command);
        }

        return $this->commandHandlers[$command::class];
    }
}

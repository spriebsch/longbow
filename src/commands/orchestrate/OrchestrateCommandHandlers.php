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

interface OrchestrateCommandHandlers
{
    const SERIALIZATION_FILE = '_LongbowCommandHandlers.php';

    public function command(string $commandClass): self;

    public function isHandledBy(string $commandHandlerClass): void;
}

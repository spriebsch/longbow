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

use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;

return [TestCommand::class => TestCommandHandlerThatReturnsEvent::class];
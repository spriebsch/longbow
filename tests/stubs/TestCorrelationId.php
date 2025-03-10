<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\tests;

use spriebsch\eventstore\CorrelationId;
use spriebsch\uuid\UUID;

class TestCorrelationId implements CorrelationId
{
    public static function from(string $uuid): CorrelationId
    {
        return new self(UUID::from($uuid));
    }

    public static function generate(): CorrelationId
    {
        return new self(UUID::generate());
    }

    public function __construct(private readonly UUID $uuid) {}

    public function asUUID(): UUID
    {
        return $this->uuid;
    }
}

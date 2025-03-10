<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\example;

use spriebsch\eventstore\CorrelationId;
use spriebsch\uuid\UUID;

final class SomeId implements CorrelationId
{
    private UUID $uuid;

    public static function generate(): self
    {
        return new self(UUID::generate());
    }

    public static function from(string $uuid): self
    {
        return new self(UUID::from($uuid));
    }

    public static function fromUUID(UUID $uuid): self
    {
        return new self($uuid);
    }

    private function __construct(UUID $uuid)
    {
        $this->uuid = $uuid;
    }

    public function asUUID(): UUID
    {
        return $this->uuid;
    }

    public function asString(): string
    {
        return $this->uuid->asString();
    }
}

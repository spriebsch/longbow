<?php declare(strict_types=1);

/*
 * This file is part of UUID.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\uuid;

final class UUID
{
    private string $uuid;

    public static function generate(): self
    {
        return new self(null);
    }

    public static function from(string $uuid): self
    {
        return new self($uuid);
    }

    private function __construct(?string $uuid)
    {
        if ($uuid === null) {
            $uuid = $this->generateUUID();
        }

        assert($uuid !== null);

        $this->ensureFormatLooksValid($uuid);

        $this->uuid = $uuid;
    }

    public function asString(): string
    {
        return $this->uuid;
    }

    private function generateUUID(): string
    {
        $uuid = bin2hex(random_bytes(16));

        assert(strlen($uuid) === 32);

        return sprintf(
            '%08s-%04s-4%03s-%04x-%012s',
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 13, 3),
            hexdec(substr($uuid, 16, 4)) & 0x3fff | 0x8000,
            substr($uuid, 20, 12)
        );
    }

    private function ensureFormatLooksValid(string $uuid): void
    {
        if (preg_match($this->pattern(), $uuid) !== 1) {
            throw UUIDException::malformedUUID($uuid);
        }
    }

    private function pattern(): string
    {
        return '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    }
}

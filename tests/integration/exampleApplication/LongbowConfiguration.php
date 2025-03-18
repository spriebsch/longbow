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

use spriebsch\diContainer\Configuration;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;
use spriebsch\longbow\LongbowConfigurationException;

class LongbowConfiguration implements Configuration
{
    public static function fromFile(File $configuration): self
    {
        return new self($configuration->require());
    }

    public static function fromArray(array $configuration): self
    {
        return new self($configuration);
    }

    private function __construct(private readonly array $configuration)
    {
        $this->ensureSettingExists('orchestrationDirectory');
        $this->ensureSettingExists('eventStore');
        $this->ensureSettingExists('longbowDatabase');
    }

    private function ensureSettingExists(string $setting): void
    {
        if (!isset($this->configuration[$setting])) {
            throw new LongbowConfigurationException($setting);
        }
    }

    public function orchestrationDirectory(): Directory
    {
        return $this->configuration['orchestrationDirectory'];
    }

    public function eventStore(): string
    {
        return $this->configuration['eventStore'];
    }

    public function longbowDatabase(): string
    {
        return $this->configuration['longbowDatabase'];
    }
}

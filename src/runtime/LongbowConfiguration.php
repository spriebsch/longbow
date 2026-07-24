<?php declare(strict_types=1);

namespace spriebsch\longbow;

use spriebsch\diContainer\Configuration;
use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;

interface LongbowConfiguration extends Configuration
{
    public function orchestrationDirectory(): Directory;

    public function topicMap(): File;

    public function sequoraDatabase(): string;

    public function longbowDatabase(): string;
}

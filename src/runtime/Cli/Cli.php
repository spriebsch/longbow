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

use spriebsch\filesystem\Directory;
use spriebsch\filesystem\File;
use spriebsch\filesystem\Filesystem;

final class Cli
{
    public static function run(array $arguments): void
    {
        $projectDirectory = getcwd();
        $sourceDirectory = $projectDirectory . '/src';
        $vendorDirectory = $projectDirectory . '/vendor';

        // detect when CLI is not run from project directory, if so show error message
        
        // load autoloader from vendor/autoload.php

        // recursively search src/ for a class implementing spriebsch\longbow;\LongbowFactory: this is the configuration

        // recursively search src/ for a class extending spriebsch\diContainer\AbstractFactory: this is the factory

        // recursively search src/folder for the file TopicMap.php

        $orchestrationDirectory = Filesystem::from($projectDirectory . '/data');
        assert($orchestrationDirectory instanceof Directory);
        $topicMap = Filesystem::from($topicMapFile);
        assert($topicMap instanceof File);

        $configuration = LongbowConfiguration::fromArray(
            $orchestrationDirectory,
            $topicMap,
            __DIR__ . '/data/sequora.db',
            __DIR__ . '/data/longbow.db',
        );

        Longbow::configure($configuration, LongbowFactory::class);
    }
}

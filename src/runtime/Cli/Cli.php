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

        if (!file_exists($projectDirectory . '/vendor/autoload.php')) {
            throw new \RuntimeException('Not in a project directory (vendor/autoload.php not found)');
        }

        $autoload = require_once $projectDirectory . '/vendor/autoload.php';

        $configurationClass = null;
        $factoryClass = null;
        $topicMapFile = null;

        $directory = new \RecursiveDirectoryIterator($projectDirectory . '/src');
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            if ($file->getFilename() === 'TopicMap.php') {
                $topicMapFile = $file->getPathname();
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            require_once $file->getPathname();

            $classes = get_declared_classes();
            foreach ($classes as $fullClassName) {
                $reflection = new \ReflectionClass($fullClassName);
                if ($reflection->isInternal()) {
                    continue;
                }

                if ($reflection->getFileName() !== $file->getPathname()) {
                    continue;
                }

                if ($reflection->implementsInterface(LongbowConfiguration::class)) {
                    $configurationClass = $fullClassName;
                }
                if ($reflection->isSubclassOf(\spriebsch\diContainer\AbstractFactory::class) && $fullClassName !== LongbowFactory::class) {
                    $factoryClass = $fullClassName;
                }
            }
        }

        if ($configurationClass === null) {
            throw new \RuntimeException('No class implementing LongbowConfiguration found in src/');
        }

        if ($factoryClass === null) {
            throw new \RuntimeException('No class extending AbstractFactory found in src/');
        }

        if ($topicMapFile === null) {
            throw new \RuntimeException('TopicMap.php not found in src/');
        }

        $orchestrationDirectory = Filesystem::from($projectDirectory . '/data');
        assert($orchestrationDirectory instanceof Directory);
        $topicMap = Filesystem::from($topicMapFile);
        assert($topicMap instanceof File);

        var_dump($orchestrationDirectory, $topicMap);

        $configuration = $configurationClass::fromArray([
            'orchestrationDirectory' => $orchestrationDirectory,
            'topicMap' => $topicMap,
            'sequoraDatabase' => $projectDirectory . '/db/sequora.db',
            'longbowDatabase' => $projectDirectory . '/db/longbow.db',
        ]);

        Longbow::configure($configuration, $factoryClass);
    }
}

<?php declare(strict_types=1);

namespace spriebsch\longbow;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Cli::class)]
#[UsesClass(Longbow::class)]
final class CliTest extends TestCase
{
    private string $tempDir;
    private string $oldCwd;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../../src/runtime/Cli/Cli.php';
        $this->tempDir = sys_get_temp_dir() . '/longbow_cli_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/src');
        mkdir($this->tempDir . '/vendor');
        file_put_contents($this->tempDir . '/vendor/autoload.php', "<?php return null;");

        // Save current working directory
        $this->oldCwd = getcwd();
        chdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        chdir($this->oldCwd);
        $this->removeDirectory($this->tempDir);
        Longbow::reset();
    }

    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function test_throws_exception_when_not_in_project_directory(): void
    {
        unlink($this->tempDir . '/vendor/autoload.php');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not in a project directory (vendor/autoload.php not found)');

        Cli::run([]);
    }

    public function test_throws_exception_when_no_configuration_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No class implementing LongbowConfiguration found in src/');

        Cli::run([]);
    }

    public function test_successfully_configures_longbow(): void
    {
        mkdir($this->tempDir . '/db');
        mkdir($this->tempDir . '/data');
        file_put_contents($this->tempDir . '/src/TopicMap.php', '<?php');
        file_put_contents($this->tempDir . '/src/Config.php', '<?php namespace Test; class Config implements \spriebsch\longbow\LongbowConfiguration { 
            public static function fromArray(array $config): self { return new self(); }
            public function orchestrationDirectory(): \spriebsch\filesystem\Directory { return \spriebsch\filesystem\Filesystem::from(getcwd()); }
            public function topicMap(): \spriebsch\filesystem\File { return \spriebsch\filesystem\Filesystem::from(getcwd() . "/src/TopicMap.php"); }
            public function sequoraDatabase(): string { return ""; }
            public function longbowDatabase(): string { return ""; }
        }');
        file_put_contents($this->tempDir . '/src/Factory.php', '<?php namespace Test; readonly class Factory extends \spriebsch\diContainer\AbstractFactory {}');

        Cli::run([]);

        $this->assertInstanceOf(\spriebsch\diContainer\Container::class, Longbow::container());
    }
}

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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LongbowExclusiveLock::class)]
#[CoversClass(AnInstanceIsAlreadyRunningException::class)]
class LongbowExclusiveLockTest extends TestCase
{
    public function test_ceates_lock(): void
    {
        $lockFile = sys_get_temp_dir() . '/test-lockfile';

        $lock = new LongbowExclusiveLock($lockFile);
        $lock->acquireLock();

        $this->assertFileExists($lockFile);

        $this->removeLockFile($lockFile);
    }

    public function test_releases_lock(): void
    {
        $lockFile = sys_get_temp_dir() . '/test-lockfile';

        $lock = new LongbowExclusiveLock($lockFile);
        $lock->acquireLock();
        $lock->releaseLock();

        $this->assertFileDoesNotExist($lockFile);
    }

    public function test_cannot_acquire_lock_twice(): void
    {
        $lockFile = sys_get_temp_dir() . '/test-lockfile';

        $lock = new LongbowExclusiveLock($lockFile);
        $lock->acquireLock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('already running');

        (new LongbowExclusiveLock($lockFile))->acquireLock();
    }

    private function removeLockFile(string $lockFile): void
    {
        unlink($lockFile);
    }
}

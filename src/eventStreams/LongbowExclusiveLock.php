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

final class LongbowExclusiveLock implements ExclusiveLock
{
    private $lockFileHandle;

    public function __construct(private string $lockFilename) {}

    public function acquireLock(): void
    {
        $this->lockFileHandle = fopen($this->lockFilename, 'w+');

        if (flock($this->lockFileHandle, LOCK_EX | LOCK_NB)) {
            fwrite($this->lockFileHandle, (string) getmypid());
        } else {
            throw new AnInstanceIsAlreadyRunningException();
        }
    }

    public function releaseLock(): void
    {
        flock($this->lockFileHandle, LOCK_UN);
        fclose($this->lockFileHandle);

        unlink($this->lockFilename);
    }
}

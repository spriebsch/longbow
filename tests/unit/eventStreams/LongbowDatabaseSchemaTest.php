<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\eventStreams;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use spriebsch\longbow\LongbowDatabaseSchema;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(LongbowDatabaseSchema::class)]
class LongbowDatabaseSchemaTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function creates_table_if_it_does_not_exist(): void
    {
        $connection = SqliteConnection::memory();

        $schema = LongbowDatabaseSchema::from($connection);
        $schema->createIfNotExists();

        $this->assertStringContainsString(
            'CREATE TABLE `positions`',
            $connection->query('SELECT sql FROM sqlite_master WHERE name="positions";')->fetchArray()[0]
        );
    }

    #[Test]
    #[Group('feature')]
    public function skips_creation_if_table_already_exists(): void
    {
        $connection = SqliteConnection::memory();

        $schema = LongbowDatabaseSchema::from($connection);
        $schema->createIfNotExists();
        $schema->createIfNotExists();

        $this->assertStringContainsString(
            'CREATE TABLE `positions`',
            $connection->query('SELECT sql FROM sqlite_master WHERE name="positions";')->fetchArray()[0]
        );
    }
}

<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\orchestration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\FakeDirectory;

#[CoversClass(LongbowPHPArraySerializer::class)]
class LongbowPHPArraySerializerTest extends TestCase
{
    public function test_serializes_to_PHP_array(): void
    {
        $array = ['key' => 'value'];

        $filename = 'the-filename';
        $directory = new FakeDirectory(__DIR__);

        $serializer = new LongbowPHPArraySerializer;
        $serializer->serialize($array, $directory, $filename);

        $files = $directory->allFiles();
        $this->assertCount(1, $files);

        $this->assertSame($array, $files[0]->require());
    }
}

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
use PHPUnit\Framework\TestCase;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\longbow\orchestration\LongbowPHPArraySerializer;
use spriebsch\longbow\tests\TestEventStream;
use spriebsch\longbow\tests\TestEventStreamProcessor;

#[CoversClass(LongbowOrchestrateEventStreamProcessors::class)]
#[CoversClass(LongbowPHPArraySerializer::class)]
class LongbowOrchestrateEventStreamProcessorsTest extends TestCase
{
    #[Group('feature')]
    public function test_configures_StreamProcessor(): void
    {
        $expectedMap = [
            TestEventStream::class => [
                TestEventStreamProcessor::id()->asString() => TestEventStreamProcessor::class
            ]
        ];

        $directory = new FakeDirectory('/not/relevant');

        $orchestration = new LongbowOrchestrateEventStreamProcessors;

        $orchestration
            ->eventStream(TestEventStream::class)
            ->isProcessedBy(TestEventStreamProcessor::class);

        $orchestration->exportOrchestrationTo($directory);

        $file = $directory->file(OrchestrateEventStreamProcessors::SERIALIZATION_FILE);

        $this->assertEquals(
            $expectedMap,
            $file->require()
        );
    }
}

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
#[CoversClass(OrchestrateEventStreamProcessorsException::class)]
#[CoversClass(EventStreamClassDoesNotExistException::class)]
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

    public function test_event_stream_class_must_exist(): void
    {
        $this->expectException(EventStreamClassDoesNotExistException::class);

        new LongbowOrchestrateEventStreamProcessors()->eventStream('does-not-exist');
    }

    public function test_event_stream_must_extend_event_stream(): void
    {
        $this->expectException(OrchestrateEventStreamProcessorsException::class);

        new LongbowOrchestrateEventStreamProcessors()->eventStream(\stdClass::class);
    }

    public function test_event_stream_must_be_selected_before_processor(): void
    {
        $this->expectException(OrchestrateEventStreamProcessorsException::class);

        new LongbowOrchestrateEventStreamProcessors()->isProcessedBy(TestEventStreamProcessor::class);
    }

    public function test_processor_must_implement_processor_interface(): void
    {
        $this->expectException(OrchestrateEventStreamProcessorsException::class);

        new LongbowOrchestrateEventStreamProcessors()
            ->eventStream(TestEventStream::class)
            ->isProcessedBy(\stdClass::class);
    }

    public function test_processor_class_must_exist(): void
    {
        $this->expectException(OrchestrateEventStreamProcessorsException::class);

        new LongbowOrchestrateEventStreamProcessors()
            ->eventStream(TestEventStream::class)
            ->isProcessedBy('does-not-exist');
    }

    public function test_processor_id_must_be_unique(): void
    {
        $orchestration = new LongbowOrchestrateEventStreamProcessors();
        $orchestration
            ->eventStream(TestEventStream::class)
            ->isProcessedBy(TestEventStreamProcessor::class);

        $this->expectException(OrchestrateEventStreamProcessorsException::class);

        $orchestration
            ->eventStream(TestEventStream::class)
            ->isProcessedBy(TestEventStreamProcessor::class);
    }
}

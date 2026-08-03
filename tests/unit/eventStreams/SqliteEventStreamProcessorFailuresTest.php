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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\DomainEvent\EventId;
use spriebsch\longbow\LongbowDatabaseSchema;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(EventStreamProcessorFailure::class)]
#[CoversClass(SqliteEventStreamProcessorFailures::class)]
#[UsesClass(LongbowDatabaseSchema::class)]
final class SqliteEventStreamProcessorFailuresTest extends TestCase
{
    public function test_failure_is_initially_absent(): void
    {
        $this->assertNull($this->failures()->failureOf(EventStreamProcessorId::generate()));
    }

    public function test_recorded_failure_contains_event_id(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $eventId = EventId::generate();
        $failures = $this->failures();
        $failures->record($processorId, $eventId, new RuntimeException('processing failed'));

        $failure = $this->failureOf($failures, $processorId);

        $this->assertTrue($eventId->equals($failure->eventId()));
    }

    public function test_recorded_failure_contains_exception_class(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $failures = $this->failures();
        $failures->record($processorId, EventId::generate(), new RuntimeException('processing failed'));

        $this->assertSame(RuntimeException::class, $this->failureOf($failures, $processorId)->exceptionClass());
    }

    public function test_recorded_failure_contains_exception_message(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $failures = $this->failures();
        $failures->record($processorId, EventId::generate(), new RuntimeException('processing failed'));

        $this->assertSame('processing failed', $this->failureOf($failures, $processorId)->exceptionMessage());
    }

    public function test_recorded_failure_contains_timestamp(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $failures = $this->failures();
        $failures->record($processorId, EventId::generate(), new RuntimeException('processing failed'));

        $this->assertNotSame('', $this->failureOf($failures, $processorId)->failedAt());
    }

    public function test_failure_can_be_recorded_without_event_id(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $failures = $this->failures();
        $failures->record($processorId, null, new RuntimeException('processing failed'));

        $this->assertNull($this->failureOf($failures, $processorId)->eventId());
    }

    public function test_failure_can_be_cleared(): void
    {
        $processorId = EventStreamProcessorId::generate();
        $failures = $this->failures();
        $failures->record($processorId, EventId::generate(), new RuntimeException('processing failed'));

        $failures->clear($processorId);

        $this->assertNull($failures->failureOf($processorId));
    }

    private function failures(): SqliteEventStreamProcessorFailures
    {
        $connection = SqliteConnection::memory();
        LongbowDatabaseSchema::from($connection)->createIfNotExists();

        return new SqliteEventStreamProcessorFailures($connection);
    }

    private function failureOf(
        EventStreamProcessorFailures $failures,
        EventStreamProcessorId       $processorId,
    ): EventStreamProcessorFailure {
        $failure = $failures->failureOf($processorId);

        if ($failure === null) {
            $this->fail('Expected processor failure was not recorded');
        }

        return $failure;
    }
}

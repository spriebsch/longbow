# Longbow 7 Migration Plan

## Dependencies and platform

- Require PHP 8.5.
- Replace `spriebsch/eventstore` with `spriebsch/sequora`.
- Use `spriebsch/domain-event` directly for event contracts and identifiers.
- Upgrade `spriebsch/di` and SQLite to their PHP 8.5-compatible release lines.
- Remove the event and identifier generators.

## Runtime and public API

- Replace EventStore event types with `DomainEvent`, `EventId`, `Topic`, and
  Sequora reader/writer types.
- Configure Sequora and its topic map through a typed Longbow configuration.
- Add a query-focused Longbow event stream backed by Sequora.
- Process envelopes so domain events reach processors and envelope IDs become
  stream positions.
- Represent processor IDs with an `AbstractId` subclass.
- Keep persistence in command handlers and synchronous dispatch in Longbow.

## Application migration

- Convert events to readonly Domain Event DTOs with `MapToTopic` and
  `UseAsCorrelationId` attributes.
- Convert application identifiers to `AbstractId` subclasses.
- Update factories, configuration, orchestration, fixtures, and examples.
- Document that EventStore database migration is outside Longbow 7.

## Verification

- Regenerate Composer autoloading.
- Run the complete PHPUnit suite.
- Run PHPStan with the repository configuration.
- Generate coverage and retain 100% production-code coverage.

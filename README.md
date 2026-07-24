# Longbow

Aim high and shoot far with Longbow, the PHP 8.5 event framework.

Longbow 7 uses [Domain Event](https://github.com/spriebsch/domain-event) for event
definitions and [Sequora](https://github.com/spriebsch/sequora) for persistence.

## Domain events

Events are readonly data objects. Topics and correlation identifiers are declared
with attributes:

```php
use spriebsch\DomainEvent\AbstractId;
use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;

final readonly class OrderId extends AbstractId
{
}

#[MapToTopic('acme.sales.order.order-placed')]
final readonly class OrderPlaced implements DomainEvent
{
    public function __construct(
        private OrderId $orderId,
        public string $product,
    ) {
    }

    #[UseAsCorrelationId]
    public function orderId(): OrderId
    {
        return $this->orderId;
    }
}
```

Generate the topic map with Domain Event's `generate-topic-map` command and
return its file from the application configuration.

## Configuration

Application configuration implements `spriebsch\longbow\LongbowConfiguration`
and supplies:

- the orchestration directory;
- the generated topic-map file;
- the Sequora SQLite database path;
- the Longbow processor-position SQLite database path.

Configure Longbow with the application factory:

```php
Longbow::configure($configuration, ApplicationFactory::class);
```

Command handlers persist their returned events through
`spriebsch\sequora\EventWriter`:

```php
public function handle(PlaceOrder $command): DomainEvent
{
    $event = new OrderPlaced(OrderId::generate(), $command->product);
    $this->eventWriter->store($event);

    return $event;
}
```

## Event streams

Streams extend Longbow's query-focused base class and define Sequora topics:

```php
final readonly class SalesEvents extends EventStream
{
    protected function topics(): array
    {
        return [Topic::fromString('acme.sales.order.order-placed')];
    }
}
```

Processors implement `EventStreamProcessor` and return an
`EventStreamProcessorId`. Longbow reads Sequora envelopes, passes their domain
events to processor methods, and stores the envelope event ID as the processor
position.

## Upgrading

Longbow 7 is a breaking release. It requires PHP 8.5 and does not migrate
databases created by `spriebsch/eventstore`; start with a fresh Sequora schema
or migrate existing event data with an application-specific migration.

(c) Stefan Priebsch.

Longbow is an event framework.

For a newer version of Longbow, Longbow 7, here is what we need to do:

- Switch from spriebsch/eventstore to spriebsch/sequora. This implicitly introduces spriebsch/domain-event
- Remove dependency on spriebsch/identifier-generator as the new identifiers are just a subclass of AbstractIdentifier from spriebsch/domain-event
- Update DI container to the latest version of spriebsch/dicontainer
- Switch from phpab to Composer autoloading and remove dependency on phpab
- Upgrade to the latest version of PHPUnit
- Make the project compatible with and requiring PHP 8.5

We work in the git branch next-generation.

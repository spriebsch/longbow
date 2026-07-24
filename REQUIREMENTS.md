Longbow is an event framework.

For a newer version of Longbow, Longbow 7, here is what we need to do:

- Switch from spriebsch/eventstore to spriebsch/sequora. This implicitly introduces spriebsch/domain-event
- Remove dependency on spriebsch/identifier-generator as the new identifiers are just a subclass of AbstractIdentifier from spriebsch/domain-event
- Remove dependency on spriebsch/event-generator
- Update DI container to the latest version of spriebsch/dicontainer
- Make the project compatible with and requiring PHP 8.5

We work in the git branch next-generation.

There is a container spriebsch/php-devbox that has the necessary tools (PHPUint, PHPStan, Infection). See AGENTS.md for instructions how to use.

For this upgrade, you do not have to follow the development process described in AGENTS.md, but create your own process as needed, however make use of the descriptions of how to use the tools in AGENTS.md.

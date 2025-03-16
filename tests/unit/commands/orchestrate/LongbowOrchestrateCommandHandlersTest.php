<?php declare(strict_types=1);

/*
 * This file is part of Longbow.
 *
 * (c) Stefan Priebsch <stefan@priebsch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spriebsch\longbow\commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use spriebsch\eventstore\Event;
use spriebsch\filesystem\FakeDirectory;
use spriebsch\filesystem\FakeFile;
use spriebsch\filesystem\File;
use spriebsch\longbow\orchestration\LongbowPHPArraySerializer;
use spriebsch\longbow\tests\TestCommand;
use spriebsch\longbow\tests\TestCommandHandlerThatReturnsEvent;

#[CoversClass(LongbowOrchestrateCommandHandlers::class)]
#[CoversClass(LongbowPHPArraySerializer::class)]
#[CoversClass(CommandHandlerClassDoesNotExistException::class)]
#[CoversClass(CommandHandlerHasNoReturnTypeException::class)]
#[CoversClass(CommandHandlerDoesNotReturnEventException::class)]
#[CoversClass(FailedToConfigureCommandHandlerException::class)]
#[CoversClass(CommandHandlerHandleMethodIsNotPublicException::class)]
#[CoversClass(CommandHandlerHandleMethodDoesNotHaveExactlyOneParameterException::class)]
#[CoversClass(ClassDoesNotImplementCommandHandlerInterfaceException::class)]
#[CoversClass(ClassDoesNotHandleCommandException::class)]
#[CoversClass(CommandHandlerHasNoHandleMethodException::class)]
#[CoversClass(DoesNotImplementCommandInterfaceException::class)]
#[CoversClass(CommandHandlerHasAlreadyBeenConfiguredException::class)]
#[CoversClass(NoCommandSpecifiedException::class)]
#[CoversClass(CommandHasNoHandlerException::class)]
#[CoversClass(FailedToConfigureCommandException::class)]
class LongbowOrchestrateCommandHandlersTest extends TestCase
{
    #[Test]
    #[Group('feature')]
    public function exports_orchestration_to_a_file(): void
    {
        $file = $this->configureCommandHandlerAndExportOrchestration();

        $this->assertInstanceOf(FakeFile::class, $file);
    }

    #[Test]
    #[Group('feature')]
    public function defines_CommandHandler_to_handle_a_command(): void
    {
        $file = $this->configureCommandHandlerAndExportOrchestration();

        $this->assertEquals(
            [TestCommand::class => TestCommandHandlerThatReturnsEvent::class],
            $file->require()
        );
    }

    #[Test]
    #[Group('exception')]
    public function command_must_be_set_before_configuring_handler(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(NoCommandSpecifiedException::class);

        $orchestration->isHandledBy(TestCommandHandlerThatReturnsEvent::class);
    }

    #[Test]
    #[Group('exception')]
    public function command_must_implement_Command_interface(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandException::class);
        $this->expectExceptionMessage('does not implement');

        $orchestration
            ->command(\stdClass::class);
    }

    #[Test]
    #[Group('exception')]
    public function command_handler_class_must_exist(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('does not exist');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy('does-not-exist');
    }

    #[Test]
    #[Group('exception')]
    public function command_handler_must_implement_CommandHandler_interface(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('does not implement');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy(\stdClass::class);
    }

    #[Test]
    #[Group('exception')]
    public function command_handler_must_have_handle_method(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $handlerWithOutHandleMethod = new class() implements CommandHandler {
        };

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('has no handle() method');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($handlerWithOutHandleMethod::class);
    }

    #[Test]
    #[Group('exception')]
    public function handle_method_of_command_handler_must_be_public(): void
    {
        $event = $this->createMock(Event::class);

        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('must be public');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($this->handlerWithoutHandleMethod($event)::class);
    }

    #[Test]
    #[Group('exception')]
    public function handle_method_of_command_handler_must_have_one_parameter(): void
    {
        $event = $this->createMock(Event::class);

        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('must have exactly one');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy(
                $this->handlerWithAdditionalParameterToHandleMethod($event)::class
            );
    }

    #[Test]
    #[Group('exception')]
    public function handle_method_of_command_handler_must_return_event(): void
    {
        $event = $this->createMock(Event::class);

        $orchestration = new LongbowOrchestrateCommandHandlers;

        $this->expectException(FailedToConfigureCommandHandlerException::class);
        $this->expectExceptionMessage('has no return type');

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy(
                $this->handlerWithoutReturnTypeToHandleMethod($event)::class
            );
    }

    #[Test]
    #[Group('exception')]
    public function command_handler_must_handle_command(): void
    {
        $event = $this->createMock(Event::class);

        $orchestration = new LongbowOrchestrateCommandHandlers;

        $handlerWithOutHandleMethod = new class($event) implements CommandHandler {
            public function __construct(private Event $event) {}

            public function handle($notTheCommand): Event
            {
                return $this->event;
            }
        };

        $this->expectException(FailedToConfigureCommandHandlerException::class);

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($handlerWithOutHandleMethod::class);
    }

    #[Test]
    #[Group('exception')]
    public function command_handler_must_return_an_event(): void
    {
        $orchestration = new LongbowOrchestrateCommandHandlers;

        $handlerWithOutHandleMethod = new class() implements CommandHandler {
            public function handle(TestCommand $command): void {}
        };

        $this->expectException(FailedToConfigureCommandHandlerException::class);

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($handlerWithOutHandleMethod::class);
    }

    #[Test]
    #[Group('exception')]
    public function only_one_handler_can_be_configured_for_a_command(): void
    {
        $event = $this->createMock(Event::class);

        $orchestration = new LongbowOrchestrateCommandHandlers;

        $handlerWithOutHandleMethod = new class($event) implements CommandHandler {
            public function __construct(private Event $event) {}

            public function handle(TestCommand $command): Event
            {
                return $this->event;
            }
        };

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($handlerWithOutHandleMethod::class);

        $this->expectException(CommandHandlerHasAlreadyBeenConfiguredException::class);

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy($handlerWithOutHandleMethod::class);
    }

    private function configureCommandHandlerAndExportOrchestration(): File
    {
        $expectedMap = [TestCommand::class => TestCommandHandlerThatReturnsEvent::class];

        $directory = new FakeDirectory('/not/relevant');

        $orchestration = new LongbowOrchestrateCommandHandlers();

        $orchestration
            ->command(TestCommand::class)
            ->isHandledBy(TestCommandHandlerThatReturnsEvent::class);

        $orchestration->exportOrchestrationTo($directory);

        return $directory->file(OrchestrateCommandHandlers::SERIALIZATION_FILE);
    }

    private function handlerWithoutHandleMethod(Event $event): CommandHandler
    {
        return new class($event) implements CommandHandler {
            public function __construct(private Event $event) {}

            protected function handle(TestCommand $command): Event
            {
                return $this->event;
            }
        };
    }

    private function handlerWithoutReturnTypeToHandleMethod(): CommandHandler
    {
        return new class implements CommandHandler {
            public function handle(TestCommand $command)
            {
            }
        };
    }

    private function handlerWithAdditionalParameterToHandleMethod(Event $event): CommandHandler
    {
        return new class($event) implements CommandHandler {
            public function __construct(private Event $event) {}

            public function handle(TestCommand $command, $anotherParameter): Event
            {
                return $this->event;
            }
        };
    }
}

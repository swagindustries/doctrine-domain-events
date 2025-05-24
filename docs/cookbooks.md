Cookbooks
=========

This section is about some things you can do while using this component.

1. [Example of password update](#password_update)
2. [Use events from another component in the domain dispatcher](#support_other_events)

Password update
---------------

This is a common use in modern applications: you have a user, it sets a password, and you want the password hashed before
it's recorded in the database.

Consider the following entity:

```php
use Doctrine\ORM\Mapping as ORM;

class User extends DomainModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    // ...
    // Other fields, maybe an email or username
    // ...
    #[ORM\Column(length: 255)]
    private string $password;

    public function setPassword(string $plainPassword): static
    {
        // Custom domain event PasswordChanged
        // see bellow for its implementation
        $this->dispatch(new PasswordChanged(
            $plainPassword,
            function (string $encodedPassword) {
                $this->password = $encodedPassword;
            }
        ));

        return $this;
    }
}
```

By sending this event you delay the job of hashing the password to an external rule, and you avoid a plain password in
your user entity!

The event would look like this:
```php
class PasswordChanged extends DomainEvent
{
    private \Closure $onPasswordChangedCallback;

    public function __construct(string $password, \Closure $onPasswordChangedCallback)
    {
        parent::__construct($password);
        $this->onPasswordChangedCallback = $onPasswordChangedCallback;
    }

    public function setEncodedPassword(string $encodedPassword): void
    {
        $onPasswordChangedCallback = $this->onPasswordChangedCallback;
        $onPasswordChangedCallback($encodedPassword);
    }
}
```

And here is the rule that will encode your password:

```php
/**
 * @implements DomainRuleInterface<PasswordChanged>
 */
class EncodePasswordRule implements DomainRuleInterface
{
    public function __construct(private PasswordHasherFactoryInterface $encoderFactory)
    {
    }

    public function on(): string
    {
        return PasswordChanged::class;
    }

    public function execute(DomainEvent $event): void
    {
        $encoder = $this->encoderFactory->getPasswordHasher(Admin::class);
        /** @var string $plainPassword */
        $plainPassword = $event->getSubject();
        $encodedPassword = $encoder->hash($plainPassword);
        $event->setEncodedPassword($encodedPassword);
    }
}
```

Support other events
--------------------

In some special cases you may have to handle some other events. For example
the workflow of Symfony dispatch events specific to this composant. You can
transform these events by redefining the dispatcher and transform the event:

```php
<?php

class WorkflowDomainEventDispatcher extends DomainEventDispatcher
{
    public function dispatch(Event $event, $eventName = null)
    {
        if ($event instanceof \Symfony\Component\Workflow\Event\Event) {
            $event = new DomainEvent($event->getSubject(), [], $event);
        }

        return parent::dispatch($event, $eventName);
    }
}
```

You can do this because the domain event support embedded events. 👍

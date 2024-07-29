Domain Event Dispatcher
=======================

The domain event dispatcher is a special dispatcher in your application that dispatch only
domain events.


Make a rule
-----------

To make a new rule (it's a listener) you should implement the `DomainRuleInterface`.

### Standalone usage

#### Add a standard rule

```php
<?php
use Biig\Component\Domain\Rule\DomainRuleInterface;
use Biig\Component\Domain\Event\DomainEvent;

$dispatcher->addRule(new class implements DomainRuleInterface {
    public function execute(DomainEvent $event)
    {
        // add some specific behavior
    }
    
    public function on()
    {
        return 'on.event';
    }
});
```

#### Add a post persist delayed rule

A post persist rule will occur only if the specified event is emitted, but only after the data is persisted in storage.
Basically flushed in the case of Doctrine.

```php
<?php
use Biig\Component\Domain\Event\DomainEvent;
use Biig\Component\Domain\Rule\PostPersistDomainRuleInterface;

$dispatcher->addRule(new class implements PostPersistDomainRuleInterface {
    public function execute(DomainEvent $event)
    {
        // add some specific behavior
    }
    
    public function after()
    {
        return 'on.event';
    }
});
```

Please notice you **need** to add some configuration to make it work:

```yaml
biig_domain:
    persist_listeners:
        # As doctrine supports many connections, you need to enable your connections one by one.
        # The most common is named "default".
        doctrine: ['default']
```

> ⚠️⚠️⚠️⚠️⚠️⚠️⚠️
> 
> Using PostPersistRule means that the flush of doctrine will (re)dispatch some events. If this is a great way to add features during your workflow...
> This also means that using a Doctrine **flush** in a domain rule (even a different one) is something tricky.
> 
> However, this package will not fail or end in infinite loop, it's 100% supported, but events order may be surprising.
> 
> ⚠️⚠️⚠️⚠️⚠️⚠️⚠️


### Symfony Integration

If you use the Symfony Bundle with autoconfiguration of your services.
**You don't have anything to do.**

If you don't auto-discover your services and don't enable autoconfiguration, then you will need to add the tag:
```yaml
My\Domain\Rule:
    tags:
        - { name: biig_domain.rule }
```

If you don't want to use the given interface or want more control on the
configuration you still can configure your service by hand:

```yaml
My\Domain\Rule:
    tags:
        # You may add many tags to add many listeners to your business rule
        - { name: biig_domain.rule, event: 'your.event.name', method: 'execute', priority: 0 }
```

_Notice: the priority field is optional as well as method._


#### Configuration reference

```yaml
biig_domain:
    # By default, the bundle will be active on all connections registered,
    # but you can specify explicitly which connections it should be enabled on.
    # Defaults to empty array []
    entity_managers: ['default']
```

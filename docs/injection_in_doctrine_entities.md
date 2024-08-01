Injection of DomainEventDispatcher in Doctrine entities
=======================================================

_This feature allows you to merge your doctrine entities with DDD model._

_To achieve this goal it provides you a set of classes to extends doctrine behavior on entity instantiation._

How it works
------------

Doctrine uses an `Instantiator` class to instantiate entities. (this is some kind of factory)

As this `Instantiator` is hardly instantiated by Doctrine, we need to extend the ORM core. Which mean
this feature **may** be in **conflict** with some other packages that may extend doctrine behavior (I don't know any).


### Usage without integration
 
```php
<?php
use Biig\Component\Domain\Event\DomainEventDispatcher;

$dispatcher = new DomainEventDispatcher();
$configuration = new \Doctrine\ORM\Configuration();
$entityManager = new \Doctrine\ORM\EntityManager($connection, $configuration);
$entityManager->getEventManager()->addEventSubscriber(new PostLoadDispatcherInjectionListener($dispatcher));
```

### Symfony integration

⚠ You need to know it
----------------------

When you use this feature, you need to keep in mind that instantiate entities by hand makes no sense. Be sure
to use at least the default instantiator. Accessible with the service `biig_domain.instantiator.default` if you use Symfony bundle.

**In debug mode**, if you don't use the instantiator to create your entities, then you'll receive a `FlushedEntityDoesntContainsDispatcherException`. 
It probably means that you're using the `new` keyword to create an entity, and then persisting it to the database. 
This exception should tell you which entity is causing the error to help your debugging.
To fix it, just follow the usages defined above.

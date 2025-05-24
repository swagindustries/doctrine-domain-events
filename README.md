Domain component
================

[![Build Status](https://github.com/swagindustries/doctrine-domain-events/actions/workflows/ci.yaml/badge.svg)](https://travis-ci.org/biig-io/DomainComponent)
[![Latest Stable Version](https://poser.pugx.org/biig/domain/v/stable)](https://packagist.org/packages/biig/domain)
[![License](https://poser.pugx.org/biig/domain/license)](https://packagist.org/packages/biig/domain)

This library is design to help you to build your application with a Domain Design Development approach.

It is well integrated with:

- Symfony >= 5.5 (for >=4.4 compatibility, install the version 2.2 of Doctrine domain events)
- ApiPlatform >= 2.1
- Doctrine >= 2.5
- PHP >= 7.4

But you can use it with any PHP project.

[Here are some slides](https://talks.nekland.fr/DoctrineDomainEvents/) that explain how we get there.

Features
--------

Domain Events:

* :bell: [Domain event dispatcher](docs/domain_event_dispatcher.md)
* :zap: [Injection of the dispatcher in Doctrine entities](docs/injection_in_doctrine_entities.md)
* :wrench: [Symfony serializer integration](docs/symfony_serializer_integration.md)
* :star: [Learn how do more with our cookbooks](docs/cookbooks.md)

Drawbacks
---------

This library is build to allow you to use Doctrine models as Domain model. This has some cost:
you can't instantiate domain model by hand anymore. This means that you need a factory for any of
the usage of your domain model.

This component provides the implementation for Symfony serializer and Doctrine. For your own
needs you should use the class (service if you use the bundle) `Biig\Component\Domain\Model\Instantiator\Instantiator`.

Installation
------------

```bash
composer require swag-industries/doctrine-domain-events
```

Basic usage
-----------

```php
class YourModel extends DomainModel
{
    public const CREATION = 'creation';
    public function __construct()
    {
        $this->dispatch(new DomainEvent($this), self::CREATION);
    }
}
```

```php
/**
 * @implements DomainRuleInterface<DomainEvent>
 */
class DomainRule implements DomainRuleInterface
{
    public function on(): string|array
    {
        return YourModel::CREATION; // Or YourCustomEvent::class that extends DomainEvent
    }
    
    public function execute(DomainEvent $event)
    {
        // Do Something on your model creation
    }
}
```

As your model needs a dispatcher you need to call the `setDispatcher()` method any time you create a new instance of your model. To avoid doing this manually you can use the `Instantiator` that the library provides.

> It doesn't use the constructor to add the dispatcher because in PHP you can create objects without the constructor. For instance, that's what Doctrine does.

```php
use Biig\Component\Domain\Model\Instantiator\Instantiator;
use Doctrine\ORM\EntityManager;

class SomeController
{
    public function index(Instantiator $instantiator, EntityManager $entityManager)
    {
        $model = $instantiator->instantiate(YourModel::class);
        $entityManager->persist($model);
        $entityManager->flush();
    }
}
```

Integration to Symfony
----------------------

Use the bundle :

```php
<?php
// config/bundles.php

return [
    // ...
    Biig\Component\Domain\Integration\Symfony\DomainBundle::class => ['all' => true],
];
```

Learn more about [Symfony Integration](/docs/domain_event_dispatcher.md#symfony-integration)

Versions
--------

| Version | Status       | Documentation | Symfony Version | PHP Version | Misc                            |
|---------|--------------|---------------|-----------------|-------------|---------------------------------|
| 1.x     | Unmaintained | [v1][v1-doc]  | >= 3.3 && <5    | >= 7.1      |                                 |
| 2.x     | Unmaintained | [v2][v2-doc]  | >= 4.3          | >= 7.4      |                                 |
| 3.x     | Unmaintained | [v3][v3-doc]  | >= 6.4          | >= 8.1      | See [UPGRADE](upgrade) guide    |
| 4.x     | Latest       | [v3][v3-doc]  | >= 6.4          | >= 8.1      | See [UPGRADE](upgrade) guide    |

[v1-doc]: https://github.com/swagindustries/doctrine-domain-events/tree/v1.5.2/docs
[v2-doc]: https://github.com/swagindustries/doctrine-domain-events/tree/v2.3.3/docs
[v3-docs]: https://github.com/swagindustries/doctrine-domain-events/tree/v3.1.2/docs
[v4-docs]: https://github.com/swagindustries/doctrine-domain-events/tree/master/docs
[upgrade]: https://github.com/swagindustries/doctrine-domain-events/tree/master/UPGRADE.md

<?php

namespace Biig\Component\Domain\Model\Instantiator;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\ModelInterface;

/**
 * Use me to instantiate domain models. So I can inject the domain dispatcher.
 *
 * If they are not models from the domain I still can instantiate them. But I
 * will not inject the domain dispatcher, I promise.
 */
class Instantiator implements DomainModelInstantiatorInterface
{
    /**
     * @var DomainEventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(DomainEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function instantiate(string $className): object
    {
        $object = new $className();
        $this->injectDispatcher($object);

        return $object;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param mixed ...$args
     * @return T
     */
    public function instantiateWithArguments(string $className, ...$args): object
    {
        $object = new $className(...$args);
        $this->injectDispatcher($object);

        return $object;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param mixed ...$args
     * @return T
     */
    public function instantiateViaStaticFactory(string $className, string $factoryMethodName, ...$args): object
    {
        $object = $className::$factoryMethodName(...$args);
        $this->injectDispatcher($object);

        return $object;
    }

    protected function injectDispatcher(object $object): void
    {
        if ($object instanceof ModelInterface) {
            $object->setDispatcher($this->dispatcher);
        }
    }
}

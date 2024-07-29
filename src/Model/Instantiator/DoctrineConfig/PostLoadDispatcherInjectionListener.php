<?php

namespace Biig\Component\Domain\Model\Instantiator\DoctrineConfig;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\ModelInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;

class PostLoadDispatcherInjectionListener
{
    public function __construct(private DomainEventDispatcherInterface $dispatcher)
    {
    }

    public function postLoad(PostLoadEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ModelInterface) {
            $entity->setDispatcher($this->dispatcher);
        }
    }
}

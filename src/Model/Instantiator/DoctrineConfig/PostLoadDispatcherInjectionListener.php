<?php

namespace Biig\Component\Domain\Model\Instantiator\DoctrineConfig;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\ModelInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class PostLoadDispatcherInjectionListener implements EventSubscriber
{
    public function __construct(private DomainEventDispatcherInterface $dispatcher)
    {
    }

    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * BC Layer: typing LifecycleEventArgs for previous Doctrine versions.
     * New versions use \Doctrine\ORM\Event\PostLoadEventArgs.
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ModelInterface) {
            $entity->setDispatcher($this->dispatcher);
        }
    }
}

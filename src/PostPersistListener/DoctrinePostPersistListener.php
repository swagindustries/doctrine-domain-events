<?php

namespace Biig\Component\Domain\PostPersistListener;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\DomainModel;
use Biig\Component\Domain\Model\ModelInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Class DoctrinePostPersistListener.
 *
 * Note: this listener is non thread safe because of Doctrine limitation.
 */
class DoctrinePostPersistListener extends AbstractBridgeListener
{
    /**
     * @var DomainModel[]
     */
    private $modelsStageForFlush;

    public function __construct(DomainEventDispatcherInterface $dispatcher)
    {
        parent::__construct($dispatcher);
        $this->modelsStageForFlush = [];
    }

    /**
     * Cache entities that are going to be flush.
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof ModelInterface) {
                $this->modelsStageForFlush[] = $entity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ModelInterface) {
                $this->modelsStageForFlush[] = $entity;
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ModelInterface) {
                $this->modelsStageForFlush[] = $entity;
            }
        }
    }

    /**
     * Entities flushed are not accessible at this point, so we take the cache.
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->modelsStageForFlush as $entity) {
            $this->getDispatcher()->persistModel($entity);
        }
    }
}

<?php

namespace Biig\Component\Domain\Tests\PostPersistListener;

use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DoctrinePostPersistListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItCallPersistForEachFlushedModel()
    {
        $model = new FakeModel();

        $dispatcher = $this->prophesize(DomainEventDispatcherInterface::class);
        $dispatcher->persistModel($model)->shouldBeCalled();

        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $unitOfWork->getScheduledEntityInsertions()->willReturn([$model]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);
        $unitOfWork->getScheduledEntityDeletions()->willReturn([]);
        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork->reveal());
        $onFlushEvent = $this->prophesize(OnFlushEventArgs::class);
        $onFlushEvent->getObjectManager()->willReturn($entityManager->reveal());

        $postPersistListener = new DoctrinePostPersistListener($dispatcher->reveal());
        $postPersistListener->onFlush($onFlushEvent->reveal());
        $postPersistListener->postFlush($this->prophesize(PostFlushEventArgs::class)->reveal());
    }

    // This is the case of doctrine fixtures load. There is nothing returned in the onFlush event
    public function testItDoesntFailIfThereIsNothingFlushed()
    {
        $listener = new DoctrinePostPersistListener(new DomainEventDispatcher());
        $listener->postFlush($this->prophesize(PostFlushEventArgs::class)->reveal());

        $this->assertInstanceOf(DoctrinePostPersistListener::class, $listener);
    }
}

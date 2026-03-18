<?php

namespace Biig\Component\Domain\Tests\Model\Instantiator;

use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Tests\SetupDatabaseTrait;
use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;

class PostLoadDispatcherInjectionListenerTest extends TestCase
{
    use SetupDatabaseTrait;

    public function testItInjectDispatcherOnStandardEntity()
    {
        $dispatcher = new DomainEventDispatcher();
        $entityManager = $this->setupDatabase($dispatcher, 'testPostLoadDispatcherInjection');
        $entity = $entityManager->getRepository(FakeModel::class)->find(1);

        $this->assertTrue($entity->hasDispatcher());
        $this->dropDatabase();
    }

    public function testItLoadsDispatcherInProxyEntity()
    {
        if (!class_exists('Doctrine\ORM\Proxy\Proxy')) {
            $this->markTestSkipped('New versions of doctrine ORM use native lazy objects');
        }
        $dispatcher = new DomainEventDispatcher();
        $entityManager = $this->setupDatabase($dispatcher, 'testItLoadsDispatcherInProxyEntity');
        $entity = $entityManager->getRepository(FakeModel::class)->find(1);

        $related = $entity->getRelated();
        $this->assertInstanceOf(Proxy::class, $related);
        $this->assertTrue($related->hasDispatcher(), 'The given proxy has no dispatcher yet');

        $this->dropDatabase();
    }
}

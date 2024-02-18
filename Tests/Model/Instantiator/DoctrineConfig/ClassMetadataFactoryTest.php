<?php

namespace Biig\Component\Domain\Tests\Model\Instantiator\DoctrineConfig;


use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadata;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadataFactory;
use Biig\Component\Domain\Tests\SetupDatabaseTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ClassMetadataFactoryTest extends TestCase
{
    use ProphecyTrait;
    use SetupDatabaseTrait;

    public function testItIsAnInstanceOfDoctrineClassMetadataFactory()
    {
        $factory = new ClassMetadataFactory();
        $this->assertInstanceOf(\Doctrine\ORM\Mapping\ClassMetadataFactory::class, $factory);
    }

    public function testItReturnAnInstanceOfClassMetadata()
    {
        $entityManager = $this->setupDatabase(new DomainEventDispatcher(), 'testItReturnAnInstanceOfClassMetadata');

        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(FakeModel::class);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);

        $this->dropDatabase();
    }

    public function testItAllowToRetrieveDomainModel()
    {
        $dispatcher = $this->prophesize(DomainEventDispatcherInterface::class);
        $dispatcher->dispatch(Argument::cetera())->shouldBeCalled();

        $entityManager = $this->setupDatabase($dispatcher->reveal(), 'testItAllowToRetrieveDomainModel');

        $res = $entityManager->getRepository(FakeModel::class)->findAll();

        /** @var FakeModel $item */
        $item = reset($res);
        $item->doAction();
        $this->dropDatabase();
    }
}

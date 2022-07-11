<?php

namespace Biig\Component\Domain\Tests\Model\Instantiator\DoctrineConfig;


use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadata;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadataFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ClassMetadataFactoryTest extends TestCase
{
    public function testItIsAnInstanceOfDoctrineClassMetadataFactory()
    {
        $factory = new ClassMetadataFactory();
        $this->assertInstanceOf(\Doctrine\ORM\Mapping\ClassMetadataFactory::class, $factory);
    }

    public function testItReturnAnInstanceOfClassMetadata()
    {
        $dbpath = \sys_get_temp_dir() . '/testItReturnAnInstanceOfClassMetadata.' . \microtime() . '.sqlite';

        $config = ORMSetup::createAnnotationMetadataConfiguration(array(__DIR__ . '/../fixtures/Entity'), true);
        $config->setClassMetadataFactoryName(ClassMetadataFactory::class);

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => $dbpath,
        ];
        $entityManager = EntityManager::create($conn, $config);
        $entityManager->getMetadataFactory()->setDispatcher(new DomainEventDispatcher());

        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(FakeModel::class);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);

        @unlink($dbpath);
    }

    public function testItAllowToRetrieveDomainModel()
    {
        $config = ORMSetup::createAnnotationMetadataConfiguration(array(__DIR__ . '/../fixtures/Entity'), true);
        $config->setClassMetadataFactoryName(ClassMetadataFactory::class);

        $dispatcher = $this->prophesize(DomainEventDispatcherInterface::class);
        $dispatcher->dispatch(Argument::cetera())->shouldBeCalled();

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../../../fixtures/dbtest/initial_fake_model.db',
        ];
        $entityManager = EntityManager::create($conn, $config);
        $entityManager->getMetadataFactory()->setDispatcher($dispatcher->reveal());

        $res = $entityManager->getRepository(FakeModel::class)->findAll();

        reset($res)->doAction();
    }
}

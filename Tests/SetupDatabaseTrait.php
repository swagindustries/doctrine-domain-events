<?php

namespace Biig\Component\Domain\Tests;

use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\PostLoadDispatcherInjectionListener;
use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Doctrine\ORM\EntityManager;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadataFactory;
use Doctrine\ORM\ORMSetup;

trait SetupDatabaseTrait
{
    private $dbPath;

    private function setupDatabase(DomainEventDispatcher $dispatcher, string $name): EntityManager
    {
        $this->dbPath = \sys_get_temp_dir() . '/'.$name.'.' . \microtime() . '.sqlite';
        copy(__DIR__ . '/fixtures/dbtest/initial_fake_model.db', $this->dbPath);

        $config = ORMSetup::createAttributeMetadataConfiguration(array(__DIR__ . '/../fixtures/Entity'), true);
        $config->setClassMetadataFactoryName(ClassMetadataFactory::class);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => $this->dbPath,
        ];

        $entityManager = EntityManager::create($conn, $config);
        $entityManager->getEventManager()->addEventSubscriber(new DoctrinePostPersistListener($dispatcher));
        $entityManager->getEventManager()->addEventSubscriber(new PostLoadDispatcherInjectionListener($dispatcher));

        $entityManager->getMetadataFactory()->setDispatcher($dispatcher);

        return $entityManager;
    }

    private function dropDatabase()
    {
        if (!$this->dbPath) {
            return;
        }

        @unlink($this->dbPath);
    }
}

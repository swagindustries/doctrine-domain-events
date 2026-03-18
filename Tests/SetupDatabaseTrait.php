<?php

namespace Biig\Component\Domain\Tests;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\PostLoadDispatcherInjectionListener;
use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\ClassMetadataFactory;
use Doctrine\ORM\ORMSetup;

trait SetupDatabaseTrait
{
    private $dbPath;

    private function setupDatabase(DomainEventDispatcherInterface $dispatcher, string $name): EntityManager
    {
        $this->dbPath = \sys_get_temp_dir() . '/'.$name.'.' . \microtime() . '.sqlite';
        copy(__DIR__ . '/fixtures/dbtest/initial_fake_model.db', $this->dbPath);

        $config = ORMSetup::createAttributeMetadataConfiguration(array(__DIR__ . '/../fixtures/Entity'), true);
        $config->enableNativeLazyObjects(PHP_VERSION_ID >= 80400);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => $this->dbPath,
        ];

        $entityManager = new EntityManager(DriverManager::getConnection($conn, $config), $config);
        $entityManager->getEventManager()->addEventListener(['postFlush', 'onFlush'], new DoctrinePostPersistListener($dispatcher));
        $entityManager->getEventManager()->addEventListener(['postLoad'], new PostLoadDispatcherInjectionListener($dispatcher));

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

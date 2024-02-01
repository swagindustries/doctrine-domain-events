<?php

// Use this script to (re)generate the initial schema

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once 'bootstrap.php';

$dbPath = __DIR__.'/fixtures/dbtest/initial_fake_model.db';

$config = ORMSetup::createAttributeMetadataConfiguration(array(__DIR__ . '/fixtures/Entity'), true);
$conn = [
    'driver' => 'pdo_sqlite',
    'path' => $dbPath,
];

$dsnParser = new \Doctrine\DBAL\Tools\DsnParser();
$connectionParams = $dsnParser->parse('sqlite3:///'.$dbPath);

$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
$entityManager = new EntityManager($connection, $config);

$schemaManager = $connection->createSchemaManager();
$schemaManager->dropDatabase($dbPath);
$schemaManager->createDatabase($dbPath);


$tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
$classes = $entityManager->getMetadataFactory()->getAllMetadata();
$tool->createSchema($classes);

$fakeModel = new \Biig\Component\Domain\Tests\fixtures\Entity\FakeModel();
$fakeModel->setFoo('test');
$fakeRelated = new \Biig\Component\Domain\Tests\fixtures\Entity\FakeModelRelation('related');
$fakeModel->setRelated($fakeRelated);
$entityManager->persist($fakeRelated);
$entityManager->persist($fakeModel);
$entityManager->flush();

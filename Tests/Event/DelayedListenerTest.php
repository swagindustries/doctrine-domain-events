<?php

namespace Biig\Component\Domain\Tests\Event;

use Biig\Component\Domain\Event\DelayedListener;
use Biig\Component\Domain\Event\DomainEvent;
use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Exception\InvalidDomainEvent;
use Biig\Component\Domain\Model\DomainModel;
use Biig\Component\Domain\Rule\PostPersistDomainRuleInterface;
use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Tests\SetupDatabaseTrait;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DelayedListenerTest extends TestCase
{
    use SetupDatabaseTrait;

    public function testICanInstantiateDelayedListener()
    {
        $delayedListener = new DelayedListener('foo', function () {});
        $this->assertInstanceOf(DelayedListener::class, $delayedListener);
    }

    public function testItProcessEventOnlyOneTime()
    {
        $count = 0;
        $delayedListener = new DelayedListener('foo', function () use (&$count) {
            ++$count;
        });

        $fakeModel = new FakeDomainModel();
        $event = new DomainEvent($fakeModel);
        $delayedListener->occur($event);
        $delayedListener->occur(new DomainEvent($fakeModel));
        $this->assertTrue($delayedListener->shouldOccur($fakeModel));

        $delayedListener->process($fakeModel);
        $this->assertFalse($delayedListener->shouldOccur($fakeModel));
        $this->assertEquals(2, $count);

        $delayedListener->process($fakeModel);
        $this->assertEquals(2, $count);

        $this->assertTrue($event->isDelayed());
    }

    public function testItFailsToRegisterOtherThanCurrentModel()
    {
        $model = new class() {
            public $foo;
        };

        $this->expectException(InvalidDomainEvent::class);
        $listener = new DelayedListener('foo', function () {});
        $listener->occur(new DomainEvent($model));
    }

    public function testItInsertInBddAfterFlushing()
    {
        $dispatcher = new DomainEventDispatcher();
        $entityManager = $this->setupDatabase($dispatcher, 'testItInsertInBddAfterFlushing');

        $model = new FakeModel();
        $model->setFoo('Model1');
        $model->setDispatcher($dispatcher);

        $rule = new class($entityManager) implements PostPersistDomainRuleInterface {
            private $entityManager;

            public function __construct(EntityManager $entityManager)
            {
                $this->entityManager = $entityManager;
            }

            public function after()
            {
                return [FakeModel::class => 'action'];
            }

            public function execute(DomainEvent $event)
            {
                $model = new FakeModel();
                $model->setFoo('RulePostPersist');
                $this->entityManager->persist($model);
                $this->entityManager->flush($model);
            }
        };
        $dispatcher->addRule($rule);

        $model->doAction();
        $entityManager->persist($model);
        $entityManager->flush($model);

        // 3 because the database was already containing 1 entry
        $this->assertEquals(3, count($entityManager->getRepository(FakeModel::class)->findAll()));
        $this->dropDatabase();
    }

    public function testItDoesNotExecuteManyTimesSameEvent()
    {
        // Test setup
        $dispatcher = new DomainEventDispatcher();
        $entityManager = $this->setupDatabase($dispatcher, 'testItDoesNotExecuteManyTimesSameEvent');

        $model = new FakeModel();
        $model->setFoo(0);
        $model->setDispatcher($dispatcher);

        $rule = new CountAndInsertRule($entityManager);
        $dispatcher->addRule($rule);

        // Test: the rule should be trigger 2 times
        $model->doAction();
        $model->doAction();
        $entityManager->persist($model);
        $entityManager->flush($model);

        $this->assertEquals(2, $model->getFoo());
        $this->dropDatabase();
    }
}

class FakeDomainModel extends DomainModel
{
}

class CountAndInsertRule implements PostPersistDomainRuleInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function after()
    {
        return [FakeModel::class => 'action'];
    }

    public function execute(DomainEvent $event)
    {
        // Count times of execution
        $event->getSubject()->setFoo($event->getSubject()->getFoo() + 1);

        // Trigger flush
        $model = new FakeModel();
        $model->setFoo('Something new to insert');
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
    }
}

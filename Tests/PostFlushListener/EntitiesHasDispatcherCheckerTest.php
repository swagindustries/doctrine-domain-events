<?php
declare(strict_types=1);

namespace Biig\Component\Domain\Tests\PostFlushListener;

use Biig\Component\Domain\Event\DomainEvent;
use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\DomainModel;
use Biig\Component\Domain\Model\Instantiator\Instantiator;
use Biig\Component\Domain\PostFlushListener\EntitiesHasDispatcherChecker;
use Biig\Component\Domain\Rule\DomainRuleInterface;
use Biig\Component\Domain\Rule\PostPersistDomainRuleInterface;
use Biig\Component\Domain\Tests\Model\FakeDomainEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\UnitOfWork;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Biig\Component\Domain\Tests\TestKernel;
use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Exception\FlushedEntityDoesntContainsDispatcherException;
require_once __DIR__ . '/../fixtures/getComplexClassHierarchyDomainModel.php';

class EntitiesHasDispatcherCheckerTest extends KernelTestCase
{
    use ProphecyTrait;

    /** @var EntityManagerInterface|ObjectProphecy */
    private $entityManger;

    protected function setUp(): void
    {
        $this->entityManger = $this->prophesize(EntityManagerInterface::class);
    }

    /* START INTEGRATION TEST */
    public function testAnEntityThatDoesntHaveDispatcherWhileFlushedThrowAnError()
    {
        self::bootKernel(['debug' => true]);
        // You should not create your entites this way in your own code !
        // Use the Biig\Component\Domain\Model\Instantiator\Instantiator service to instantiate your entities.
        $model = new FakeModel();

        $this->expectException(FlushedEntityDoesntContainsDispatcherException::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($model);
        $entityManager->flush();
    }

    public function testItDoesntCheckIfNotDebug()
    {
        self::bootKernel(['debug' => false]);
        // You should not create your entites this way in your own code !
        // Use the Biig\Component\Domain\Model\Instantiator\Instantiator service to instanciate your entities.
        $model = new FakeModel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($model);
        $entityManager->flush();
        $this->assertTrue(true); // We except FlushedEntityDoesntContainsDispatcher exception not to be thrown
    }

    public function testItDoesNothingIfDispatcherIsHereAndDebugIsEnabled()
    {
        self::bootKernel(['debug' => true]);

        /** @var Instantiator $instantiator */
        $instantiator = self::getContainer()->get('biig_domain.instantiator.default');
        $model = $instantiator->instantiate(FakeModel::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($model);
        $entityManager->flush();
        $this->assertTrue(true); // We except FlushedEntityDoesntContainsDispatcher exception not to be thrown
    }
    /* END INTEGRATION TEST */

    /* START UNIT TEST */
    public function testIfGivenEntityIsntADomainModelItDoesNothing()
    {
        $this->entityManger->getClassMetadata(Argument::any())->shouldNotBeCalled();

        $subject = new EntitiesHasDispatcherChecker();
        $subject->postFlush($this->mockEvent([
            'someFQCN' => [new \stdClass()],
        ]));
    }

    public function testThatADomainModelWithDispatcherIsOk()
    {
        $this->entityManger->getClassMetadata(Argument::any())->shouldNotBeCalled();

        $subject = new EntitiesHasDispatcherChecker();
        $instantitor = new Instantiator($this->prophesize(DomainEventDispatcherInterface::class)->reveal());
        $subject->postFlush($this->mockEvent([
            FakeModel::class => [$instantitor->instantiate(FakeModel::class)],
        ]));
    }

    public function testThatADomainModelWithoutDispatcherThrownAnError()
    {
        $metadata = $this->prophesize(ClassMetadata::class);
        $metadata->getIdentifier()->willReturn(['id']);
        $this->entityManger->getClassMetadata(FakeModel::class)->willReturn($metadata->reveal())->shouldBeCalled();
        $this->expectException(FlushedEntityDoesntContainsDispatcherException::class);

        $subject = new EntitiesHasDispatcherChecker();
        $subject->postFlush($this->mockEvent([
            FakeModel::class => [new FakeModel(),],
        ]));
    }

    public function testThatAComplexDomainClassHierarchyModelWithoutDispatcherThrownAnError()
    {
        $metadata = $this->prophesize(ClassMetadata::class);
        $metadata->getIdentifier()->willReturn(['id']);
        $this->entityManger->getClassMetadata(Argument::any())->willReturn($metadata->reveal())->shouldBeCalled();
        $this->expectException(FlushedEntityDoesntContainsDispatcherException::class);

        $subject = new EntitiesHasDispatcherChecker();
        $subject->postFlush($this->mockEvent([
            'someFqcn' => [getComplexModel(),],
        ]));
    }

    public function testItDoesNothingWhenAProxyIsGiven()
    {
        $proxy = $this->prophesize(Proxy::class)->reveal();
        $subject = new EntitiesHasDispatcherChecker();

        $this->entityManger->getClassMetadata(Argument::cetera())->shouldNotBeCalled();

        $subject->postFlush($this->mockEvent([
            'someFqcn' => [$proxy],
        ]));
    }

    private function mockEvent(array $identityMap = []): PostFlushEventArgs
    {
        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $this->entityManger->getUnitOfWork()->willReturn($unitOfWork->reveal());
        $unitOfWork->getIdentityMap()->willReturn($identityMap);

        return new PostFlushEventArgs($this->entityManger->reveal());
    }
    /* END UNIT TEST */
}

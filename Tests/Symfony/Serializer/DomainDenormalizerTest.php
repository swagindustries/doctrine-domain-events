<?php

namespace Biig\Component\Domain\Tests\Symfony\DependencyInjection\Serializer;

use Biig\Component\Domain\Tests\fixtures\Entity\FakeModel;
use Biig\Component\Domain\Event\DomainEventDispatcher;
use Biig\Component\Domain\Integration\Symfony\Serializer\DomainDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DomainDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectNormalizer|ObjectProphecy
     */
    private $decorated;

    /**
     * @var DomainEventDispatcher
     */
    private $dispatcher;

    public function setUp(): void
    {
        $this->decorated = $this->prophesize(NormalizerInterface::class);
        $this->decorated->willImplement(DenormalizerInterface::class);
        $this->dispatcher = new DomainEventDispatcher();
    }

    public function testItIsAnInstanceOfDenormalize()
    {
        $denormalizer = new DomainDenormalizer($this->decorated->reveal(), $this->dispatcher);
        $this->assertInstanceOf(DenormalizerInterface::class, $denormalizer);
    }

    public function testItDoesntSupportsDenormalization()
    {
        $this->decorated->supportsDenormalization(Argument::cetera())->willReturn(false);
        $denormalizer = new DomainDenormalizer($this->decorated->reveal(), $this->dispatcher);

        $this->assertFalse($denormalizer->supportsDenormalization([], \stdClass::class, ''));
        $this->assertFalse($denormalizer->supportsDenormalization([], FakeModel::class, ''));
    }

    public function testItSupportsDenormalization()
    {
        $this->decorated->supportsDenormalization(Argument::cetera())->willReturn(true);
        $denormalizer = new DomainDenormalizer($this->decorated->reveal(), $this->dispatcher);

        $this->assertTrue($denormalizer->supportsDenormalization([], \stdClass::class, ''));
        $this->assertTrue($denormalizer->supportsDenormalization([], FakeModel::class, ''));
    }

    public function testDenormalize()
    {
        $fake = $this->prophesize(FakeModel::class);

        $this->decorated->denormalize([], FakeModel::class, null, [])->willReturn($fake)->shouldBeCalled();
        $fake->setDispatcher($this->dispatcher)->shouldBeCalled();

        $denormalizer = new DomainDenormalizer($this->decorated->reveal(), $this->dispatcher);
        $denormalizer->denormalize([], FakeModel::class, null, []);
    }
}

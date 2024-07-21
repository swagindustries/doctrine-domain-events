<?php

namespace Biig\Component\Domain\Integration\Symfony\Serializer;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\ModelInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DomainDenormalizer.
 */
final class DomainDenormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    /**
     * @var ObjectNormalizer
     */
    private $decorated;

    /**
     * @var DomainEventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(NormalizerInterface $decorated, DomainEventDispatcherInterface $dispatcher)
    {
        $this->decorated = $decorated;
        $this->dispatcher = $dispatcher;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $domain = $this->decorated->denormalize($data, $class, $format, $context);

        if (is_subclass_of($class, ModelInterface::class)) {
            $domain->setDispatcher($this->dispatcher);
        }

        return $domain;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    /**
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $this->decorated->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

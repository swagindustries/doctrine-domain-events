<?php
declare(strict_types=1);

namespace Biig\Component\Domain\PostFlushListener;

use Biig\Component\Domain\Event\DomainEventDispatcherInterface;
use Biig\Component\Domain\Model\Instantiator\Instantiator;
use Biig\Component\Domain\Model\ModelInterface;
use Biig\Component\Domain\Exception\FlushedEntityDoesntContainsDispatcherException;
use Biig\Component\Domain\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\Event\PostFlushEventArgs;

final class EntitiesHasDispatcherChecker
{
    public function postFlush(PostFlushEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $this->checkModelEntityHasDispatcher($entity, $entityManager);
            }
        }
    }

    private function checkModelEntityHasDispatcher(object $entity, EntityManagerInterface $entityManager): void
    {
        if (!$entity instanceof ModelInterface) {
            return;
        }

        $dispatcher = $this->objectPropertyThief($entity, 'dispatcher');
        if ($dispatcher instanceof DomainEventDispatcherInterface) {
            return;
        }

        $metadata = $entityManager->getClassMetadata($className = get_class($entity));

        $idsProperties = $metadata->getIdentifier();
        $identifiers = ': ';
        foreach ($idsProperties as $property) {
            $idPropertyValue = $this->objectPropertyThief($entity, $property);
            $identifiers .= "$property => $idPropertyValue,";
        }

        $identifiers = rtrim($identifiers, ',');

        throw new FlushedEntityDoesntContainsDispatcherException($identifiers, $className);
    }


    private function objectPropertyThief(object $object, string $property)
    {
        if (property_exists($object, $property)) {
            // This method is copy/pasted from @see https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
            // It allow to retrieve a private property from object fastly
            // Reflection is slow, we don't want to annoy the developers :D
            $sweetsThief = function ($object) use($property) {
                return $object->{$property};
            };
            $sweetsThief = \Closure::bind($sweetsThief, null, $object);

            return $sweetsThief($object);
        }

        // When the property is private in a parent (Ex: when you extends DomainModel class), we have to use reflection
        $ref = new \ReflectionClass($object);
        do {
            try {
                $propertyRef = $ref->getProperty($property);
                $propertyRef->setAccessible(true);
                // TODO when php 7.4 is the minimum version we could check that the property's type is DomainEventDispatcherInterface

                return $propertyRef->getValue($object);
            } catch (\ReflectionException $exception) {}
        } while ($ref = $ref->getParentClass());

        throw new InvalidArgumentException("Property $property doesn't exists in class hierarchy.");
    }
}

<?php

namespace Biig\Component\Domain\Integration\Symfony\DependencyInjection\CompilerPass;

use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterListenersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array<string,string> $connections */
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        $supportedConnections = $container->getParameter('biig_domain.entity_managers');

        // Doctrine bundle is probably not installed!
        if (empty($entityManagers)) {
            return;
        }

        foreach ($entityManagers as $entityManagerName => $entityManagerServiceId) {
            if (!empty($supportedConnections) && !in_array($entityManagerName, $supportedConnections)) {
                continue;
            }

            $container
                ->setDefinition(
                    sprintf('biig_domain.post_persist_listener.doctrine_%s', $entityManagerName),
                    new Definition(DoctrinePostPersistListener::class)
                )
                ->setArgument(0, new Reference('biig_domain.dispatcher'))
                ->addTag('doctrine.event_listener', ['event' => 'postFlush', 'entity_manager' => $entityManagerName])
                ->addTag('doctrine.event_listener', ['event' => 'onFlush', 'entity_manager' => $entityManagerName])
            ;

            $container
                ->getDefinition('biig_domain.postload_listener')
                ->addTag('doctrine.event_listener', ['event' => 'postLoad', 'entity_manager' => $entityManagerName])
            ;
        }
    }
}

<?php

namespace Biig\Component\Domain\Integration\Symfony\DependencyInjection;

use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Biig\Component\Domain\Rule\RuleInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DomainExtension extends Extension implements PrependExtensionInterface
{
    public const DOMAIN_RULE_TAG = 'biig_domain.rule';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        if (class_exists('Symfony\\Bundle\\WebProfilerBundle\\DependencyInjection\\WebProfilerExtension') && $container->getParameter('kernel.debug')) {
            $loader->load('services.debug.yaml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(RuleInterface::class)->addTag(self::DOMAIN_RULE_TAG);

        $container->setParameter('biig_domain.entity_managers', $config['entity_managers']);

        if (!empty($config['persist_listeners']['doctrine'])) {
            $this->registerDoctrinePostPersistListener($config['persist_listeners']['doctrine'], $container);
        }
    }

    /**
     * This may fail if a bundle (registered after this one) or a compiler pass modify the parameter.
     * The `VerifyDoctrineConfigurationCompilerPass` verify configuration integrity.
     */
    public function prepend(ContainerBuilder $container): void
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            // Pre-process the configuration
            $configs = $container->getExtensionConfig($this->getAlias());
            $config = $this->processConfiguration(new Configuration(), $configs);
        }
    }

    public function getAlias(): string
    {
        return 'biig_domain';
    }

    private function registerDoctrinePostPersistListener(array $config, ContainerBuilder $container)
    {
        foreach ($config as $connection) {
            $container
                ->autowire(
                    sprintf('biig_domain.post_persist_listener.doctrine_%s', $connection),
                    DoctrinePostPersistListener::class
                )
                ->setArgument(0, new Reference('biig_domain.dispatcher'))
                ->addTag('doctrine.event_subscriber', ['connection' => $connection])
            ;
        }
    }
}

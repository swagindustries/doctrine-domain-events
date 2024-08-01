<?php

namespace Biig\Component\Domain\Integration\Symfony\DependencyInjection;

use Biig\Component\Domain\Rule\RuleInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DomainExtension extends Extension
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
    }

    public function getAlias(): string
    {
        return 'biig_domain';
    }
}

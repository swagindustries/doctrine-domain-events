<?php

namespace Biig\Component\Domain\Tests\Symfony\DependencyInjection;

use Biig\Component\Domain\Integration\Symfony\DependencyInjection\DomainExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class DomainExtensionTest extends TestCase
{
    public function testItAddsAParameterForEntityManagersSupported()
    {
        $extension = new DomainExtension();

        $config = [[
            // notice that null would be resolved to empty array by the config component
            'entity_managers' => ['default', 'foo'],
        ]];

        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
        ]));
        $extension->load($config, $container);

        $this->assertEquals(['default', 'foo'], $container->getParameter('biig_domain.entity_managers'));
    }

    public function testItDoesntRegisterDoctrinePostPersistListenerToContainer()
    {
        $extension = new DomainExtension();

        $config = [[]];

        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false
        ]));
        $extension->load($config, $container);

        $this->assertFalse($container->hasDefinition('biig_domain.post_persist_listener.doctrine_default'));
    }

    public function testItSetEntityManagersConfigAsParameterOfContainer()
    {
        $extension = new DomainExtension();

        $config = [[
            'entity_managers' => [
                'default',
                'customManager',
            ],
        ]];

        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false
        ]));
        $extension->load($config, $container);

        $this->assertTrue($container->hasParameter('biig_domain.entity_managers'));
        $this->assertEquals($container->getParameter('biig_domain.entity_managers'), ['default', 'customManager']);
    }

    public function testItRegisterTraceableDomainEventDispatcherInDev()
    {
        $extension = new DomainExtension();
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => true
        ]));
        $extension->load([], $container);
        $this->assertTrue($container->hasDefinition('Biig\Component\Domain\Debug\TraceableDomainEventDispatcher'));
        $this->assertTrue($container->hasDefinition('Biig\Component\Domain\DataCollector\DomainEventDataCollector'));
    }
}

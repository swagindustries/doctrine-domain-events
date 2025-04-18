<?php

namespace Biig\Component\Domain\Integration\Symfony;

use Biig\Component\Domain\Integration\Symfony\DependencyInjection\CompilerPass\EnableDomainDenormalizerCompilerPass;
use Biig\Component\Domain\Integration\Symfony\DependencyInjection\CompilerPass\RegisterDomainRulesCompilerPass;
use Biig\Component\Domain\Integration\Symfony\DependencyInjection\CompilerPass\RegisterListenersCompilerPass;
use Biig\Component\Domain\Integration\Symfony\DependencyInjection\DomainExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DomainBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DomainExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Must be before RegisterEventListenersAndSubscribersPass
        $container->addCompilerPass(new RegisterListenersCompilerPass(), priority: 30);

        $container->addCompilerPass(new RegisterDomainRulesCompilerPass());
        $container->addCompilerPass(new EnableDomainDenormalizerCompilerPass());
    }
}

<?php

namespace Biig\Component\Domain\Tests\Symfony\DependencyInjection\CompilerPass;

use Biig\Component\Domain\Model\Instantiator\DoctrineConfig\PostLoadDispatcherInjectionListener;
use Biig\Component\Domain\PostPersistListener\DoctrinePostPersistListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegisterListenersCompilerPassTest extends KernelTestCase
{
    public function testItRegistersThePostLoadListener(): void
    {
        self::bootKernel();

        $this->assertInstanceOf(PostLoadDispatcherInjectionListener::class, $this->getContainer()->get('biig_domain.postload_listener'));
        $this->assertInstanceOf(DoctrinePostPersistListener::class, $this->getContainer()->get('biig_domain.post_persist_listener.doctrine_default'));
    }
}

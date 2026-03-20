<?php
declare(strict_types=1);

namespace Biig\Component\Domain\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        if (PHP_VERSION_ID < 80400) {
            $loader->load(__DIR__ . '/../Tests/config/symfony_test_kernel_config.yaml');
        } else {
            $loader->load(__DIR__ . '/../Tests/config/symfony_test_kernel_config_php_8.4.yaml');
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * BC Layer for Symfony 4.4.
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }
}


<?php
namespace Guttmann\NautCli;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $configDirectory = dirname(__DIR__) . '/config';

        $loader->load($configDirectory . '/services.yml');
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle()
        ];
    }

    public function configureRoutes(RouteCollectionBuilder $routes)
    {
        // none
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/naut-cli/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/naut-cli/logs';
    }

}

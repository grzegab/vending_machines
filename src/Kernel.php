<?php

declare(strict_types=1);

namespace App;

use App\Model\CandyCatalog;
use App\Model\CandyVendingMachine;
use App\Persistence\InMemoryCandyCatalog;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', []);
        $services = $container->services()
            ->defaults()
                ->autowire()
                ->autoconfigure()
                ->private()
        ;

        $services->load('App\\', __DIR__ . '/*')
            ->exclude([
                __DIR__ . '/Kernel.php',
            ]);

        $services->alias(Machine\MachineInterface::class, CandyVendingMachine::class);
        $services->alias(CandyCatalog::class, InMemoryCandyCatalog::class);
    }

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}

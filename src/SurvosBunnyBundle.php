<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\BunnyBundle;

use Survos\BunnyBundle\Command\BunnyListCommand;
use Survos\BunnyBundle\Controller\BunnyController;
use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosBunnyBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceId = 'survos_bunny.bunny_service';
        $container->services()->alias(BunnyService::class, $serviceId);
        $builder->autowire($serviceId, BunnyService::class)
            ->setArgument('$apiKey', $config['api_key'])
            ->setArgument('$readonlyPassword', $config['readonly_password'])
            ->setArgument('$password', $config['password'])
            ->setArgument('$storageZone', $config['storage_zone'])
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(BunnyController::class)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
        ;

        foreach ([BunnyListCommand::class] as $commandName) {
            $builder->autowire($commandName)
                ->setAutoconfigured(true)
                ->addTag('console.command')
            ;
        }


        // twig classes, for bunny_url
        /*
        $definition = $builder
        ->autowire('survos.barcode_twig', BarcodeTwigExtension::class)
        ->addTag('twig.extension');

        */
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_key')->defaultValue(null)->end()
                ->scalarNode('readonly_password')->defaultValue(null)->end()
                ->scalarNode('password')->defaultValue(null)->end()
                ->scalarNode('storage_zone')->defaultValue(null)->end()
//            ->integerNode('cache')->defaultValue('1h')->end()
            ->end();
    }
}

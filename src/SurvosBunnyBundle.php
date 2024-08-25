<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\BunnyBundle;

use Survos\BunnyBundle\Service\BunnyService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosBunnyBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceId = 'survos_bunny.bunny_service';
        $container->services()->alias(BunnyService::class, $serviceId);
        $builder->autowire($serviceId, BunnyService::class)
            ->setArgument('$apiKey', $config['api_key'])
            ->setArgument('$storageZone', $config['storage_zone'])
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(BunnCon::class)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
            ->setArgument('$bus', new Reference('debug.traced.messenger.bus.default', ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->setArgument('$chartBuilder', new Reference('chartjs.builder', ContainerInterface::NULL_ON_INVALID_REFERENCE))
        ;

        // $builder->setParameter('survos_workflow.direction', $config['direction']);

        // twig classes

        /*
        $definition = $builder
        ->autowire('survos.barcode_twig', BarcodeTwigExtension::class)
        ->addTag('twig.extension');

        $definition->setArgument('$widthFactor', $config['widthFactor']);
        $definition->setArgument('$height', $config['height']);
        $definition->setArgument('$foregroundColor', $config['foregroundColor']);
        */
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_key')->defaultValue(null)->end()
                ->scalarNode('storage_zone')->defaultValue(null)->end()
//            ->integerNode('cache')->defaultValue('1h')->end()
            ->end();
    }
}

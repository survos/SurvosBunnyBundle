<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\BunnyBundle;

use Survos\BunnyBundle\Command\BunnyConfigCommand;
use Survos\BunnyBundle\Command\BunnyListCommand;
use Survos\BunnyBundle\Command\BunnySyncCommand;
use Survos\BunnyBundle\Controller\BunnyController;
use Survos\BunnyBundle\Service\BunnyService;
use Survos\SimpleDatatables\SurvosSimpleDatatablesBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        // get all bundles
        $bundles = $builder->getParameter('kernel.bundles');
        $hasSimpleDatatables = in_array(SurvosSimpleDatatablesBundle::class, array_values($bundles));

        $serviceId = 'survos_bunny.bunny_service';
        $container->services()->alias(BunnyService::class, $serviceId);
        $builder->autowire($serviceId, BunnyService::class)
            ->setArgument('$config', $config)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(BunnyController::class)
            ->setArgument('$simpleDatatablesInstalled', $hasSimpleDatatables)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
        ;

        foreach ([BunnyConfigCommand::class, BunnyListCommand::class, BunnySyncCommand::class] as $commandName) {
            $builder->autowire($commandName)
                ->setAutoconfigured(true)
                ->addTag('console.command')
            ;
        }


        // twig classes, for bunny_url ?
        /*
        $definition = $builder
        ->autowire('survos.barcode_twig', BarcodeTwigExtension::class)
        ->addTag('twig.extension');

        */
    }

    private function addZonesSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('zones')
            ->arrayPrototype()
            ->children()
                ->scalarNode('id')->end()
                ->scalarNode('region')->end()
                ->scalarNode('readonly_password')->end()
                ->scalarNode('password')->end()
            ->end()
            ->end()
            ->end();

    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('api_key')->defaultValue(null)->end()
                ->scalarNode('storage_zone')->defaultValue(null)->end()
                ->scalarNode('region')->defaultValue(null)->end()
                ->scalarNode('readonly_password')->defaultValue(null)->end()
                ->scalarNode('password')->defaultValue(null)->end()
            ->end();

        $this->addZonesSection($rootNode);
    }

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    public function prepend(ContainerBuilder $container): void
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');
        dd($bundles);
        // determine if AcmeGoodbyeBundle is registered
        if (!isset($bundles['AcmeGoodbyeBundle'])) {
            // disable AcmeGoodbyeBundle in bundles
            $config = ['use_acme_goodbye' => false];
            foreach ($container->getExtensions() as $name => $extension) {
                match ($name) {
                    // set use_acme_goodbye to false in the config of
                    // acme_something and acme_other
                    //
                    // note that if the user manually configured
                    // use_acme_goodbye to true in config/services.yaml
                    // then the setting would in the end be true and not false
                    'acme_something', 'acme_other' => $container->prependExtensionConfig($name, $config),
                    default => null
                };
            }
        }

        // get the configuration of AcmeHelloExtension (it's a list of configuration)
        $configs = $container->getExtensionConfig($this->getAlias());

        // iterate in reverse to preserve the original order after prepending the config
        foreach (array_reverse($configs) as $config) {
            // check if entity_manager_name is set in the "acme_hello" configuration
            if (isset($config['entity_manager_name'])) {
                // prepend the acme_something settings with the entity_manager_name
                $container->prependExtensionConfig('acme_something', [
                    'entity_manager_name' => $config['entity_manager_name'],
                ]);
            }
        }
    }
}

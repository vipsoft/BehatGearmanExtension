<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Behat\Extension\ExtensionInterface;

/**
 * Gearman extension for Behat class.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class Extension implements ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
        $loader->load('core.xml');

        $container->setParameter('behat.gearman.gearman_server', $config['gearman_server']);
        $container->setParameter('behat.gearman.task_name', $config['task_name']);
        $container->setParameter('behat.gearman.custom_task_names', $config['custom_task_names']);
        $container->setParameter('behat.gearman.access_token', $config['access_token']);
        $container->setParameter('behat.gearman.compression', $config['compression']);

        if (isset($config['command_class'])) {
            $class = $config['command_class'];

            if (substr($class, 0, 1) !== '\\') {
                $class = '\\' . $class;
            }

            $container->setParameter('behat.console.command.class', $class);
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                scalarNode('gearman_server')->
                    defaultNull()->
                end()->
                scalarNode('task_name')->
                    defaultValue('behat')->
                end()->
                arrayNode('custom_task_names')->
                    prototype('scalar')->end()->
                end()->
                scalarNode('access_token')->
                    defaultNull()->
                end()->
                booleanNode('compression')->
                    defaultFalse()->
                end()->
                scalarNode('command_class')->
                    isRequired()->
                end()->
            end()->
        end();
    }

    /**
     * Returns compiler passes used by mink extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        return array(
        );
    }
}

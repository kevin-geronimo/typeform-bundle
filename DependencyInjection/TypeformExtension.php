<?php

namespace TypeformBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TypeformExtension.
 */
class TypeformExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configurationLoaded = new Configuration();
        $config = $this->processConfiguration($configurationLoaded, $configs);

        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $containerBuilder->addCompilerPass(new RegisterListenersPass());

        $forms = array_splice($config, array_search('forms', array_keys($config)))['forms'];
        $parameters = $config;

        // Defined Configuration
        $container->setDefinition('typeform.configuration', new Definition('TypeformBundle\Core\Configuration', array(
            $parameters,
            new Reference('router')
        )));

        // Define Client
        $container->setDefinition('typeform.client', new Definition('TypeformBundle\Core\Client', array(
            new Reference('typeform.configuration'),
        )));

        // Define Form Manager
        $container->setDefinition('typeform.manager', new Definition('TypeformBundle\Core\Manager', array(
            $forms,
            new Reference('typeform.client'),
        )));

        $container->setAlias('typeform', 'typeform.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'typeform';
    }
}

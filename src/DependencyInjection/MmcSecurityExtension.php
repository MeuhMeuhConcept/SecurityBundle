<?php

namespace Mmc\Security\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MmcSecurityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('security.mmc.service.session_ttl_provider');
        $definition->replaceArgument(0, $config['sessionTTL']);

        $definition = $container->getDefinition('security.mmc.logout.listener');
        foreach ($config['logout'] as $firewall) {
            $definition->addTag('kernel.event_subscriber', [
                'event' => 'Symfony\Component\Security\Http\Event\LogoutEvent',
                'dispatcher' => 'security.event_dispatcher.'.$firewall,
            ]);
        }
    }
}

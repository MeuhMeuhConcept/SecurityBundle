<?php

namespace Mmc\Security\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MmcLoginFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('type_path', 'type');
        $this->addOption('key_path', 'username');
        $this->defaultFailureHandlerOptions = [];
        $this->defaultSuccessHandlerOptions = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'mmc-login';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, string $id, array $config, string $userProviderId)
    {
        $authenticatorIds = $config['authenticators'];
        $authenticatorReferences = [];
        foreach ($authenticatorIds as $authenticatorId) {
            $authenticatorReferences[] = new Reference($authenticatorId);
        }

        $authenticators = new IteratorArgument($authenticatorReferences);

        $provider = 'security.authentication.provider.mmc.'.$id;
        $container
            ->setDefinition($provider, new ChildDefinition('security.authentication.provider.mmc'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, $authenticators)
        ;

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'security.authentication.listener.mmc';
    }

    /**
     * {@inheritdoc}
     */
    protected function isRememberMeAware($config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createListener(ContainerBuilder $container, string $id, array $config, string $userProvider)
    {
        $listenerId = $this->getListenerId();
        $listener = new ChildDefinition($listenerId);
        $listener->replaceArgument(3, $id);
        $listener->replaceArgument(4, isset($config['success_handler']) ? new Reference($this->createAuthenticationSuccessHandler($container, $id, $config)) : null);
        $listener->replaceArgument(5, isset($config['failure_handler']) ? new Reference($this->createAuthenticationFailureHandler($container, $id, $config)) : null);
        $listener->replaceArgument(6, array_intersect_key($config, $this->options));

        $listenerId .= '.'.$id;
        $container->setDefinition($listenerId, $listener);

        return $listenerId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node
            ->children()
                ->arrayNode('authenticators')
                    ->info('An array of service ids for all of your "authenticators"')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }
}

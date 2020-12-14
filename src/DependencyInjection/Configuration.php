<?php

namespace Mmc\Security\DependencyInjection;

use Mmc\Security\Entity\Enum\AuthType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('mmc_security');

        $node = $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('sessionTTL')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('default')->defaultValue(3600)->end()
            ;

        foreach (AuthType::getConstants() as $type) {
            $node->integerNode($type)->end();
        }
        $node
                    ->end()
                ->end()
                ->arrayNode('logout')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ;

        return $treeBuilder;
    }
}

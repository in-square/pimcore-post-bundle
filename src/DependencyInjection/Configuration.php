<?php

declare(strict_types=1);

namespace InSquare\PimcorePostBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('in_square_pimcore_post');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line Symfony returns NodeBuilder, but the interface misses scalarNode().
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('post_root_folder')
                    ->defaultValue('/posts')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('sorting')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('date_field')
                            ->defaultValue('date')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('archive')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('idle_minutes')
                            ->defaultValue(10)
                            ->min(1)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

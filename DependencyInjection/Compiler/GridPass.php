<?php

namespace APY\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class GridPass.
 *
 * @author  Quentin Ferrer
 */
class GridPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('apy_grid.registry')) {
            return;
        }

        $definition = $container->getDefinition('apy_grid.registry');

        $types = $container->findTaggedServiceIds('apy_grid.type');
        foreach ($types as $id => $tag) {
            $definition->addMethodCall('addType', [new Reference($id)]);
        }

        $columns = $container->findTaggedServiceIds('apy_grid.column');
        foreach ($columns as $id => $tag) {
            $definition->addMethodCall('addColumn', [new Reference($id)]);
        }

        $sources = $container->findTaggedServiceIds('apy_grid.source');
        foreach ($sources as $id => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('addSource', [$tag['alias'], new Reference($id)]);
            }
        }

        $exports = $container->findTaggedServiceIds('apy_grid.export');
        foreach ($exports as $id => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('addExport', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}

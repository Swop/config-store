<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterConfigViewAdaptersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('config_store.config_view.config_view_dumper')) {
            return;
        }

        $definition = $container->getDefinition(
            'config_store.config_view.config_view_dumper'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'config_store.config_view_dumper.adapter'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'registerAdapter',
                array(new Reference($id))
            );
        }
    }
}

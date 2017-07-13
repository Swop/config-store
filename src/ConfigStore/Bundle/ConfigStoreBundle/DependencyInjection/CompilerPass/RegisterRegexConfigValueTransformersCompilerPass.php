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

class RegisterRegexConfigValueTransformersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('config_store.config_view.config_value_transformer.regex')) {
            return;
        }

        $definition = $container->getDefinition(
            'config_store.config_view.config_value_transformer.regex'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'config_store.config_view_dumper.regex.transformer'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes["priority"])) {
                    $params = array(new Reference($id), $attributes["priority"]);
                } else {
                    $params = array(new Reference($id));
                }

                $definition->addMethodCall(
                    'registerTransformer',
                    $params
                );
            }
        }
    }
}

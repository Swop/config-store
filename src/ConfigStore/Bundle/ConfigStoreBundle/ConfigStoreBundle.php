<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle;

use ConfigStore\Bundle\ConfigStoreBundle\DependencyInjection\CompilerPass\RegisterRegexConfigValueTransformersCompilerPass;
use ConfigStore\Bundle\ConfigStoreBundle\DependencyInjection\CompilerPass\RegisterConfigViewAdaptersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConfigStoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterConfigViewAdaptersCompilerPass());
        $container->addCompilerPass(new RegisterRegexConfigValueTransformersCompilerPass());
    }
}

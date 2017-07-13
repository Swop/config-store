<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView\Adapter;

use ConfigStore\ConfigView\AbstractConfigViewAdapter;
use ConfigStore\ConfigView\DumpContext;
use ConfigStore\Model\App;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhpConfigView extends AbstractConfigViewAdapter
{
    /**
     * {@inheritDoc}
     */
    public function supportsFormat($format)
    {
        return 'php' === $format;
    }

    /**
     * {@inheritDoc}
     */
    public function dump(DumpContext $dumpContext)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($dumpContext->getDumpOptions());

        $configs = $this->getTransformedAppConfigValues($dumpContext);

        $serializedContent = str_replace("'", "\\'", serialize($configs));

        if (!$options['useDefineStatements']) {
            $tpl = <<< EOF
<?php
return unserialize('%s');
EOF;
        } else {
            $tpl = <<< EOF
<?php
\$config = unserialize('%s');

foreach (\$config as \$key => \$value) {
    if (!defined(\$key)) {
        if (is_array(\$value)) {
            // Constants can't have an array as a value...
            // The serialized value is therefore keep as the constant value
            \$value = serialize(\$value);
        }

        define(\$key, \$value);
    }
}

return \$config;
EOF;
        }

        return sprintf($tpl, $serializedContent);
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType()
    {
        return 'application/x-php';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    private function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            array(
                'useDefineStatements'
            )
        );

        $resolver->setDefaults(
            array(
                'useDefineStatements' => 'false'
            )
        );

        $resolver->setAllowedTypes(
            array(
                'useDefineStatements' => array('string')
            )
        );

        $resolver->setNormalizer(
            'useDefineStatements',
            function (Options $options, $value) {
                return $value === 'true';
            }
        );
    }
}

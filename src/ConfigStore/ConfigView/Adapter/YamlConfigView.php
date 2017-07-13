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
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Yaml\Yaml;

class YamlConfigView extends AbstractConfigViewAdapter
{
    /** @var SerializerInterface $serializer */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsFormat($format)
    {
        return 'yaml' === $format;
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

        if (null === $options['rootNode']) {
            $data = $configs;
        } else {
            $data = array($options['rootNode'] => $configs);
        }

        $context = SerializationContext::create()->setSerializeNull(true);

        return $this->serializer->serialize($data, 'yml', $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType()
    {
        return 'application/x-yaml';
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    private function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            array(
                'rootNode'
            )
        );

        $resolver->setDefaults(
            array(
                'rootNode' => 'parameters'
            )
        );

        $resolver->setAllowedTypes(
            array(
                'rootNode' => array('null', 'string')
            )
        );
    }
}

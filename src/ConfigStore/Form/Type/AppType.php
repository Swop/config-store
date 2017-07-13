<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Form\Type;

use ConfigStore\ApiKey\ApiKeyGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppType extends AbstractType
{
    /** @var ApiKeyGenerator */
    private $apiKeyGenerator;

    /**
     * @param ApiKeyGenerator $apiKeyGenerator
     */
    public function  __construct(ApiKeyGenerator $apiKeyGenerator)
    {
        $this->apiKeyGenerator = $apiKeyGenerator;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('group', 'app_group_selector')
            ->add(
                'configItems',
                'collection',
                [
                    'type'         => 'configItem',
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'prototype'    => true,
                    'by_reference' => false,
                ]
            )
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $method = strtoupper($form->getConfig()->getMethod());

                if ('POST' === $method) {
                    if (!array_key_exists('configItems', $data)) {
                        $data['configItems'] = [];
                    }
                }

                $event->setData($data);
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'      => '\ConfigStore\Model\App',
                'csrf_protection' => false,
            )
        );

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app';
    }
}

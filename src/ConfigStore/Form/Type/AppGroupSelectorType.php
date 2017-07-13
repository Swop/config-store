<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Form\Type;

use ConfigStore\Form\DataTransformer\AppGroupToGroupIdTransformer;
use ConfigStore\Manager\AppManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppGroupSelectorType extends AbstractType
{
    /** @var AppManager */
    private $appManager;

    /**
     * @param AppManager $appManager
     */
    public function __construct(AppManager $appManager)
    {
        $this->appManager = $appManager;
    }


    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new AppGroupToGroupIdTransformer($this->appManager));
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'empty_data' => null,
                'invalid_message' => 'The selected group does not exist',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'app_group_selector';
    }
}

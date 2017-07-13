<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Form\DataTransformer;

use ConfigStore\Exception\UnknownGroupException;
use ConfigStore\Manager\AppManager;
use ConfigStore\Model\AppGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AppGroupToGroupIdTransformer implements DataTransformerInterface
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
     * Transforms an object (AppGroup) to a string (group id).
     *
     * @param  AppGroup|null $appGroup
     *
     * @return int
     */
    public function transform($appGroup)
    {
        if (null === $appGroup) {
            return "";
        }

        return $appGroup->getId();
    }

    /**
     * Transforms a string (group id) to an object (AppGroup).
     *
     * @param  int $groupId
     * @return AppGroup|null
     *
     * @throws TransformationFailedException if object (AppGroup) is not found.
     */
    public function reverseTransform($groupId)
    {
        if (!$groupId) {
            return null;
        }

        try {
            $group = $this->appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            throw new TransformationFailedException($e->getMessage(), 0, $e);
        }

        return $group;
    }
}

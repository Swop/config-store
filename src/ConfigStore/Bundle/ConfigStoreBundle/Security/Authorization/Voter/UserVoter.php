<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Security\Authorization\Voter;

use ConfigStore\Model\User;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

/**
 * Class ViewUserVoter
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Security\Authorization\Voter
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class UserVoter extends AbstractVoter
{
    /** View a user account */
    const VIEW = 'view';
    /** Edit a user account */
    const EDIT = 'edit';

    /**
     * {@inheritDoc}
     */
    protected function getSupportedAttributes()
    {
        return [self::VIEW, self::EDIT];
    }

    /**
     * {@inheritDoc}
     */
    protected function getSupportedClasses()
    {
        return ['\ConfigStore\Model\User'];
    }

    /**
     * {@inheritDoc}
     */
    protected function isGranted($attribute, $subject, $user = null)
    {
        if (!$user instanceof User || !$subject instanceof User) {
            return false;
        }

        // User wants to edit/view his own account
        if ($user->getId() === $subject->getId()) {
            return true;
        }

        // User is an admin
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}

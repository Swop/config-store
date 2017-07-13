<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Manager;

use ConfigStore\Exception\UnknownUserException;
use ConfigStore\Model\User;
use ConfigStore\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Class UserManager
 *
 * @package \ConfigStore\Manager
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class UserManager
{
    /** @var UserRepository */
    private $userRepository;
    /** @var ObjectManager */
    protected $persistenceManager;
    /** @var EncoderFactoryInterface */
    protected $encoderFactory;

    /**
     * @param UserRepository          $userRepository
     * @param ObjectManager           $persistenceManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        UserRepository $userRepository,
        ObjectManager $persistenceManager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->userRepository     = $userRepository;
        $this->persistenceManager = $persistenceManager;
        $this->encoderFactory     = $encoderFactory;
    }

    /**
     * @param string $value
     *
     * @return User
     *
     * @throws UnknownUserException
     */
    public function getByUsernameOrEmail($value)
    {
        if (null === $user = $this->userRepository->findByUsernameOrEmail($value)) {
            throw new UnknownUserException($value);
        }

        return $user;
    }

    /**
     * @param string $slug
     *
     * @return User
     *
     * @throws UnknownUserException
     */
    public function getBySlug($slug)
    {
        if (null === $user = $this->userRepository->findoneBy(['slug' => $slug])) {
            throw new UnknownUserException($slug);
        }

        return $user;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $name
     * @param bool   $active
     * @param bool   $admin
     *
     * @return User
     */
    public function create($username, $password, $email, $name, $active, $admin)
    {
        $user = new User();
        $user
            ->setUsername($username)
            ->setRawPassword($password)
            ->setEmail($email)
            ->setName($name)
            ->setEnabled((bool)$active)
            ->setAdmin((bool)$admin)
        ;

        $this->save($user);

        return $user;
    }

    /**
     * @param User $user
     */
    public function activate(User $user)
    {
        $user->setEnabled(true);

        $this->save($user);
    }

    /**
     * @param User $user
     */
    public function deactivate(User $user)
    {
        $user->setEnabled(false);

        $this->save($user);
    }

    /**
     * @param User   $user
     * @param string $password
     */
    public function changePassword(User $user, $password)
    {
        $user->setRawPassword($password);

        $this->save($user);
    }

    /**
     * @param User $user
     */
    public function promoteToAdmin(User $user)
    {
        $user->setAdmin(true);

        $this->save($user);
    }

    /**
     * @param User $user
     */
    public function demote(User $user)
    {
        $user->setAdmin(false);

        $this->save($user);
    }

    /**
     * Save a user
     *
     * @param User $user
     */
    public function save(User $user)
    {
        $this->updatePassword($user);

        $this->persistenceManager->persist($user);
        $this->persistenceManager->flush();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->userRepository->findAll();
    }

    /**
     * @param User   $user
     * @param string $password
     *
     * @return bool
     */
    public function isValidPassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $encodedPassword = $encoder->encodePassword($password, $user->getSalt());

        return $encodedPassword === $user->getPassword();

    }

    /**
     * @param User $user
     */
    private function updatePassword(User $user)
    {
        if (0 !== strlen($password = $user->getRawPassword())) {
            $encoder = $this->encoderFactory->getEncoder($user);
            $salt    = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

            $user->setSalt($salt);
            $user->setPassword($encoder->encodePassword($password, $salt));
            $user->eraseCredentials();
        }
    }
}

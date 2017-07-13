<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 *
 * @package \ConfigStore\Model
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class User implements UserInterface
{
    /** @var  int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var string $email */
    private $email;
    /** @var string $username */
    private $username;
    /** @var string $slug */
    private $slug;
    /** @var string $password */
    private $password;
    /** @var string $salt */
    private $salt;
    /** @var string $rawPassword */
    private $rawPassword;
    /** @var bool */
    private $admin;
    /** @var bool */
    private $enabled;

    /**
     * {inheritDoc}
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        if ($this->isAdmin()) {
            $roles[] = 'ROLE_ADMIN';
        }

        return $roles;
    }

    /**
     * {inheritDoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {inheritDoc}
     */
    public function eraseCredentials()
    {
        $this->rawPassword = null;
    }

    /**
     * Gets the email attribute
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Gets the id attribute
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the name attribute
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the slug attribute
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getRawPassword()
    {
        return $this->rawPassword;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $rawPassword
     *
     * @return $this
     */
    public function setRawPassword($rawPassword)
    {
        $this->rawPassword = $rawPassword;

        return $this;
    }

    /**
     * @param string $salt
     *
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param boolean $admin
     *
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @param boolean $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin;
    }
}

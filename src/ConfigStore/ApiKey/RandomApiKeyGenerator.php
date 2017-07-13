<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ApiKey;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class RandomApiKeyGenerator implements ApiKeyGenerator
{
    /** @var SecureRandomInterface */
    private $secureRandom;

    /**
     * @param SecureRandomInterface $secureRandom
     */
    public function  __construct(SecureRandomInterface $secureRandom)
    {
        $this->secureRandom = $secureRandom;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        return rtrim(strtr(base64_encode($this->secureRandom->nextBytes(50)), '+/', '-_'), '=');
    }
}

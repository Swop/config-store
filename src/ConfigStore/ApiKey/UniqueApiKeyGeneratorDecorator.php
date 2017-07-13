<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ApiKey;

use ConfigStore\Manager\AppManager;

class UniqueApiKeyGeneratorDecorator implements ApiKeyGenerator
{
    /** @var AppManager */
    private $appManager;
    /** @var ApiKeyGenerator */
    private $wrapped;

    /**
     * @param AppManager      $appManager
     * @param ApiKeyGenerator $wrapped
     */
    public function __construct(AppManager $appManager, ApiKeyGenerator $wrapped)
    {
        $this->appManager = $appManager;
        $this->wrapped    = $wrapped;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        do {
            $generatedAccessKey = $this->wrapped->generate();
        } while ($this->appManager->isValidAccessKey($generatedAccessKey));

        return $generatedAccessKey;
    }
}

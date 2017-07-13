<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ApiKey;

interface ApiKeyGenerator
{
    /**
     * Generate an api key
     *
     * @return string
     */
    public function generate();
}

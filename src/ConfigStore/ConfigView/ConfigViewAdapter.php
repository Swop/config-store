<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\ConfigView;

interface ConfigViewAdapter
{
    /**
     * Checks if the apadter supports the given format
     *
     * @param string $format
     *
     * @return bool
     */
    public function supportsFormat($format);

    /**
     * Dump the app configuration to the required format string
     *
     * @param DumpContext $dump
     *
     * @return string
     *
     */
    public function dump(DumpContext $dump);

    /**
     * Get the content-type to associate to the response
     *
     * @return string
     */
    public function getContentType();
}

<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use ConfigStore\ConfigView\ConfigViewDumper;
use ConfigStore\Model\App;
use Symfony\Component\HttpFoundation\Request;

class ConfigViewHandler extends ViewHandler
{
    /** @var ConfigViewDumper $configViewDumper */
    private $configViewDumper;

    /**
     * @param ConfigViewDumper $dumper
     */
    public function setConfigViewDumper(ConfigViewDumper $dumper)
    {
        $this->configViewDumper = $dumper;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(View $view, Request $request = null)
    {
        if (null === $request) {
            $request = $this->container->get('request');
        }

        $format = $view->getFormat() ?: $request->getRequestFormat();

        if (!$view->getData() instanceof App || !$this->configViewDumper->supportsFormat($format)) {
            return parent::handle($view, $request);
        }

        return $this->configViewDumper->forgeConfigurationResponse(
            $format,
            $view->getData(),
            $request->get('dump_options', array()),
            $request->get('env', array()),
            $view->getHeaders()
        );
    }
}

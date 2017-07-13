<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class MainMenuController
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class MainMenuController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderMainMenuAction()
    {
        $appManager = $this->get('config_store.manager.app');

        $groups = $appManager->allGroups();

        return $this->render('ConfigStoreBundle:Admin/Menu:main_menu.html.twig', ['groups' => $groups]);
    }
}

<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin;

use ConfigStore\Exception\UnknownAppException;
use ConfigStore\Exception\UnknownConfigKeyException;
use ConfigStore\Manager\AppManager;
use ConfigStore\Manager\ConfigManager;
use ConfigStore\Model\App;
use ConfigStore\Model\ConfigItem;
use TokenSecuredAction\Manager\TokenSecuredActionManager;
use JMS\Serializer\SerializationContext;
use ConfigStore\Exception\UnknownGroupException;
use ConfigStore\Model\AppGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TokenSecuredAction\Annotation\TokenSecured;

/**
 * Class GroupController
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class GroupController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        return $this->render('ConfigStoreBundle:Admin/Group:list.html.twig');
    }

    /**
     * @return Response
     */
    public function listAjaxAction()
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        $groups         = $appManager->allGroups();
        $standaloneApps = $appManager->getStandaloneApps();

        $context = SerializationContext::create()->setGroups('AppGroup');

        return new Response(
            $this->get('jms_serializer')->serialize(
                [
                    'groups' => $groups,
                    'standaloneApps' => $standaloneApps
                ],
                'json',
                $context
            ),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param string $groupId
     *
     * @return Response
     */
    public function getAction($groupId)
    {
        $appManager = $this->get('config_store.manager.app');

        $context = SerializationContext::create()->setGroups('AppGroup');

        if ($groupId === 'other') {
            return new Response(
                $this->get('jms_serializer')->serialize(
                    ['apps' => $appManager->getStandaloneApps()],
                    'json',
                    $context
                ),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } else {
            return new Response(
                $this->get('jms_serializer')->serialize(
                    $appManager->getGroup($groupId),
                    'json',
                    $context
                ),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }
    }

    /**
     * @param Request $request
     *
     * @TokenSecured("createGroup")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $group = new AppGroup();

        $form = $this->createForm("appGroup", $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('config_store.manager.app')->saveGroup($group);

            $context = SerializationContext::create()->setGroups('AppGroup');

            return new Response(
                $this->get('jms_serializer')->serialize($group, 'json', $context),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        }

        return new JsonResponse(
            ['error' => 'Invalid form data', 'formErrors' => $form->getErrors()],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param Request $request
     * @param int     $groupId
     *
     * @TokenSecured("updateGroup")
     *
     * @return Response
     */
    public function updateAction(Request $request, $groupId)
    {
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm("appGroup", $group, ['method' => 'PUT']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appManager->saveGroup($group);

            $context = SerializationContext::create()->setGroups('AppGroup');

            return new Response(
                $this->get('jms_serializer')->serialize($group, 'json', $context),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        }

        return new JsonResponse(
            ['error' => 'Invalid form data', 'formErrors' => $form->getErrors()],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param Request $request
     * @param string  $groupId
     *
     * @TokenSecured("deleteGroup")
     *
     * @return Response
     */
    public function deleteAction(Request $request, $groupId)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $apps = null;
        if ($request->query->get('deleteGroupApps') === '1') {
            $apps = $group->getApps()->getValues();
        }

        $appManager->deleteGroup($group);

        if (null !== $apps) {
            $appManager->deleteMultipleApps($apps);
        }

        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @param string  $groupId
     *
     * @TokenSecured("setRef")
     *
     * @return Response
     */
    public function updateRefAction(Request $request, $groupId)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $appSlug = $request->request->get('appSlug');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        if ($app->isRef()) {
            return new JsonResponse(['success' => true], Response::HTTP_OK);
        }

        try {
            $group->setReference($app);
            $appManager->saveGroup($group);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param string $groupId
     *
     * @TokenSecured("dropRef")
     *
     * @return Response
     */
    public function dropRefAction($groupId)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $group->setReference(null);

        $appManager->saveGroup($group);

        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $groupId
     *
     * @return Response
     */
    public function listOneGroupAction($groupId)
    {
        return $this->render("ConfigStoreBundle:Admin/App:list.html.twig", ['groupId' => $groupId]);
    }

    /**
     * @param Request $request
     * @param string  $groupId
     *
     * @TokenSecured("dispatchConfig")
     *
     * @return Response
     */
    public function dispatchConfigAction(Request $request, $groupId)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $configKey    = $request->request->get('configKey');
        $configValues = $request->request->get('configValues');

        $appsToSave = [];

        /** @var App $app */
        foreach ($group->getApps() as $app) {
            if (!array_key_exists($app->getSlug(), $configValues)) {
                continue;
            }

            try {
                $configItem = $app->getConfigItem($configKey);
                $configItem
                    ->setValue($configValues[$app->getSlug()]);
            } catch (UnknownConfigKeyException $e) {
                $configItem = new ConfigItem();
                $configItem
                    ->setApp($app)
                    ->setKey($configKey)
                    ->setValue($configValues[$app->getSlug()])
                ;
                $app->addConfigItem($configItem);
            }

            $appsToSave[] = $app;
        }

        $this->get('config_store.manager.app')->saveMultipleApps($appsToSave);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param $groupId
     * @param $configItemKey
     *
     * @return Response
     */
    public function getCompetitorConfigItemsAction($groupId, $configItemKey)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $group = $appManager->getGroup($groupId);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        /** @var ConfigManager $configManager */
        $configManager = $this->get('config_store.manager.config');
        $otherConfigItems = $configManager->getCompetitorConfigItems($configItemKey, $group);

        return new Response(
            $this->get('jms_serializer')->serialize(
                $otherConfigItems,
                'json',
                SerializationContext::create()->setGroups('ConfigItem')
            ),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}

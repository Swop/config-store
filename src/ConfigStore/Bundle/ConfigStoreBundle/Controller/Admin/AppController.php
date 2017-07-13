<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin;

use ConfigStore\ConfigView\ConfigViewDumper;
use ConfigStore\Exception\UnknownGroupException;
use ConfigStore\Manager\AppManager;
use ConfigStore\Manager\ConfigManager;
use ConfigStore\Model\ConfigItem;
use JMS\Serializer\SerializationContext;
use ConfigStore\Exception\UnknownAppException;
use ConfigStore\Model\App;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TokenSecuredAction\Annotation\TokenSecured;

/**
 * Class AppController
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class AppController extends Controller
{
    /**
     * @param string $appSlug
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function viewAction($appSlug)
    {
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $context = SerializationContext::create()->setGroups('App');

        return new Response(
            $this->get('jms_serializer')->serialize($app, 'json', $context),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param string $appSlug
     *
     * @return Response
     */
    public function viewWebAction($appSlug)
    {
        return $this->render('ConfigStoreBundle:Admin/App:app_config_edit.html.twig', ['appSlug' => $appSlug]);
    }

    /**
     * @param Request $request
     * @param string  $appSlug
     *
     * @TokenSecured("updateApp")
     *
     * @return Response
     */
    public function updateAction(Request $request, $appSlug)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $originalConfigItems = [];
        foreach ($app->getConfigItems() as $configItem) {
            $originalConfigItems[] = $configItem;
        }

        $form = $this->createForm(
            "app",
            $app,
            ['validation_groups' => ['single_app_edition'], 'method' => 'PUT']
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($app->getConfigItems() as $configItem) {
                /** @var ConfigItem $toDel */
                foreach ($originalConfigItems as $key => $toDel) {
                    if ($toDel->getKey() === $configItem->getKey()) {
                        unset($originalConfigItems[$key]);
                    }
                }
            }

            /** @var ConfigItem $configItem */
            foreach ($originalConfigItems as $configItem) {
                $configItem->getApp()->removeConfigItem($configItem);
            }

            $this->get('config_store.manager.config')->deleteMultiple($originalConfigItems);
            $appManager->saveApp($app);

            $context = SerializationContext::create()->setGroups('App');

            return new Response(
                $this->get('jms_serializer')->serialize($app, 'json', $context),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        return new JsonResponse(
            ['error' => 'Invalid form data', 'formErrors' => $form->getErrors()],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $appSlug
     *
     * @TokenSecured("deleteApp")
     *
     * @return Response
     */
    public function deleteAction($appSlug)
    {
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $appManager->deleteApp($app);

        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @TokenSecured("createApp")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        $fromApp = null;
        if (null !== $fromAppSlug = $request->query->get('duplicateConfigFromApp')) {
            try {
                $fromApp = $appManager->getBySlug($fromAppSlug);
            } catch (UnknownAppException $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
        }

        $app = new App();

        $form = $this->createForm("app", $app);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appManager->saveApp($app);

            if (null !== $fromApp) {
                /** @var ConfigManager $configManager */
                $configManager = $this->get('config_store.manager.config');

                $configManager->duplicateConfig($fromApp, $app);
                $appManager->saveApp($app);
            }

            $context = SerializationContext::create()->setGroups('App');

            return new Response(
                $this->get('jms_serializer')->serialize($app, 'json', $context),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        }

        return new JsonResponse(
            ['error' => 'Invalid request', 'formErrors' => $form->getErrors()],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $appSlug
     * @param string $groupId
     *
     * @TokenSecured("moveToGroup")
     *
     * @return Response
     */
    public function moveAction($appSlug, $groupId)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
            $group = $appManager->getGroup($groupId);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (UnknownGroupException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        if ($app->isRef()) {
            return new JsonResponse(
                ['error' => "A ref application can't be moved to an other group"],
                Response::HTTP_NOT_ACCEPTABLE
            );
        }

        $app->setGroup($group);

        $appManager->saveApp($app);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * Perform a diff between configuration of the given two apps
     *
     * @param string $appSlug
     * @param string $otherAppSlug
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function diffAction($appSlug, $otherAppSlug)
    {
        return $this->render(
            'ConfigStoreBundle:Admin/App:app_config_edit.html.twig',
            ['appSlug' => $appSlug, 'comparedAppSlug' => $otherAppSlug]
        );
    }

    /**
     * @param string $appSlug
     *
     * @return Response
     */
    public function previewAction($appSlug)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        /** @var $viewDumper ConfigViewDumper */
        $viewDumper = $this->get('config_store.config_view.config_view_dumper');

        $preview = [];

        $supportedFormats = [
            ['id' => 'json', 'title' => 'JSON'],
            ['id' => 'php', 'title' => 'PHP'],
            ['id' => 'yaml', 'title' => 'YAML']
        ];

        foreach ($supportedFormats as $formatConfig) {
            $preview[$formatConfig['title']] = $viewDumper
                ->forgeConfigurationResponse($formatConfig['id'], $app)
                ->getContent()
            ;
        }

        return new JsonResponse($preview, Response::HTTP_OK);
    }

    /**
     * @param string $appSlug
     *
     * @TokenSecured("revokeApiKey")
     *
     * @return Response
     */
    public function revokeApiKeyAction($appSlug)
    {
        /** @var AppManager $appManager */
        $appManager = $this->get('config_store.manager.app');

        try {
            $app = $appManager->getBySlug($appSlug);
        } catch (UnknownAppException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $appManager->revokeAccessKey($app);

        return new JsonResponse(['access_key' => $app->getAccessKey()], Response::HTTP_OK);
    }
}

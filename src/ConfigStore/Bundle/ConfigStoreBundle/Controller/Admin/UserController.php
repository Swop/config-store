<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin;

use ConfigStore\Exception\UnknownUserException;
use ConfigStore\Manager\UserManager;
use ConfigStore\Model\User;
use TokenSecuredAction\Manager\TokenSecuredActionManager;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use TokenSecuredAction\Annotation\TokenSecured;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class UserController
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class UserController extends Controller
{
    /**
     * @return Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listWebAction()
    {
        return $this->render('ConfigStoreBundle:Admin/User:list.html.twig');
    }

    /**
     * @return Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction()
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        $users = $userManager->getAll();

        $context = SerializationContext::create()->setGroups('User');

        return new Response(
            $this->get('jms_serializer')->serialize($users, 'json', $context),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param string $userSlug
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @TokenSecured("activateUser")
     *
     * @return Response
     */
    public function activateAction($userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $userManager->activate($user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param string $userSlug
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @TokenSecured("deactivateUser")
     *
     * @return Response
     */
    public function deactivateAction($userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $userManager->deactivate($user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @TokenSecured("createUser")
     */
    public function createAction(Request $request)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        $user = new User();

        $form = $this->createForm("user", $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($user);

            $context = SerializationContext::create()->setGroups('User');

            return new Response(
                $this->get('jms_serializer')->serialize($user, 'json', $context),
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
     * @param Request $request
     * @param string  $userSlug
     *
     * @TokenSecured("updateUser")
     *
     * @return Response
     */
    public function updateAction(Request $request, $userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        $errorMessage = '';
        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            $user = null;
            $errorMessage = $e->getMessage();
        }

        // We first check if the user have the authorization to access to this feature
        $authChecker = $this->get('security.authorization_checker');

        if (false === $authChecker->isGranted('edit', $user)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        if (null === $user) {
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm("user", $user, ['method' => 'PUT']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($user);

            $context = SerializationContext::create()->setGroups('User');

            return new Response(
                $this->get('jms_serializer')->serialize($user, 'json', $context),
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
     * @param string $userSlug
     *
     * @return Response
     */
    public function viewAction($userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        $errorMessage = '';
        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            $user = null;
            $errorMessage = $e->getMessage();
        }

        // We first check if the user have the authorization to access to this feature
        $authChecker = $this->get('security.authorization_checker');

        if (false === $authChecker->isGranted('view', $user)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        if (null === $user) {
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        $context = SerializationContext::create()->setGroups('User');

        return new Response(
            $this->get('jms_serializer')->serialize($user, 'json', $context),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @return Response
     */
    public function getMeAction()
    {
        $context = SerializationContext::create()->setGroups('User');

        return new Response(
            $this->get('jms_serializer')->serialize($this->getUser(), 'json', $context),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param string $userSlug
     *
     * @return Response
     */
    public function viewWebAction($userSlug)
    {
        return $this->render('ConfigStoreBundle:Admin/User:view.html.twig', ['userSlug' => $userSlug]);
    }

    /**
     * @param string $userSlug
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @TokenSecured("promoteUser")
     *
     * @return Response
     */
    public function promoteAction($userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $userManager->promoteToAdmin($user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param string $userSlug
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @TokenSecured("demoteUser")
     *
     * @return Response
     */
    public function demoteAction($userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        $userManager->demote($user);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param string  $userSlug
     *
     * @TokenSecured("changePassword")
     *
     * @return Response
     */
    public function changePasswordAction(Request $request, $userSlug)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('config_store.manager.user');

        $errorMessage = '';
        try {
            $user = $userManager->getBySlug($userSlug);
        } catch (UnknownUserException $e) {
            $user = null;
            $errorMessage = $e->getMessage();
        }

        // We first check if the user have the authorization to access to this feature
        $authChecker = $this->get('security.authorization_checker');

        if (false === $authChecker->isGranted('edit', $user)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        if (null === $user) {
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        $oldPassword = null;
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            $oldPassword = $request->request->get('oldPassword');
            if (null === $oldPassword) {
                return new JsonResponse(
                    ['error' => 'You must provide the old password in order to change it'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!$userManager->isValidPassword($user, $oldPassword)) {
                return new JsonResponse(
                    ['error' => 'Invalid password'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $newPassword = $request->request->get('newPassword');

        $userManager->changePassword($user, $newPassword);

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}

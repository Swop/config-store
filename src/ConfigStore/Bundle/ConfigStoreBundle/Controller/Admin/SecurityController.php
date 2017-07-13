<?php
/*
 * (c) Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * Please view the LICENSE file for the full copyright and license information.
 */
namespace ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin;

use TokenSecuredAction\Manager\TokenSecuredActionManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class SecurityController
 *
 * @package \ConfigStore\Bundle\ConfigStoreBundle\Controller\Admin
 *
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 */
class SecurityController extends Controller
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        if ($username = $session->get(SecurityContextInterface::LAST_USERNAME)) {
            $session->remove(SecurityContextInterface::LAST_USERNAME);
        }

        if ($error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        }

        $loginForm = $this->createForm('login');
        $loginForm
            ->add('login', 'submit');

        return $this->render('ConfigStoreBundle:Security:login.html.twig', [
            'last_username' => $username,
            'error'         => $error,
            'loginForm'     => $loginForm->createView()
        ]);
    }

    /**
     * @param string $namespace
     *
     * @return Response
     */
    public function getSecurityTokensAction($namespace)
    {
//        /** @var AuthorizationChecker $authChecker */
//        $authChecker = $this->get('security.authorization_checker');
//
//        if ($authChecker->isGranted('ROLE_ADMIN')) {
//            $intentions = array_merge($intentions, [
//                'createUser',
//                'activateUser',
//                'deactivateUser',
//                'promoteUser',
//                'demoteUser',
//            ]);
//        }

        /** @var TokenSecuredActionManager $tokenSecuredActionManager */
        $tokenSecuredActionManager = $this->get('token_secured_action.manager');

        $intentions = [
            'app' => [
                'updateApp',
                'deleteApp',
                'createApp',
                'moveToGroup',
                'revokeApiKey'
            ],
            'group' => [
                'createGroup',
                'updateGroup',
                'deleteGroup',
                'setRef',
                'dropRef',
                'dispatchConfig'
            ],
            'user' => [
                'activateUser',
                'deactivateUser',
                'createUser',
                'updateUser',
                'promoteUser',
                'demoteUser',
                'changePassword'
            ],
        ];

        $namespaceIntentions = array_key_exists($namespace, $intentions) ? $intentions[$namespace] : [];

        if (empty($namespaceIntentions)) {
            return new JsonResponse(
                ['error' => sprintf('Invalid namespace: %s', $namespace)],
                Response::HTTP_NOT_FOUND
            );
        }

        $securityTokens = array_reduce(
            $namespaceIntentions,
            function ($carry, $item) use ($tokenSecuredActionManager) {
                $carry[$item] = $tokenSecuredActionManager->generateToken($item);

                return $carry;
            },
            []
        );

        return new JsonResponse(
            $securityTokens,
            Response::HTTP_OK
        );
    }
}

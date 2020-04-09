<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Entity\Organisation;
use App\Enum\OrganisationCategoryType;
use App\Form\Organisation\ExternalOrganisationType;
use App\Form\PasswordContainer\LoginType;
use App\Security\OpenIdConnect\ClientInterface;
use App\Security\UserProvider;
use App\Service\Interfaces\AuthenticationServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/login")
 */
class LoginController extends BaseFormController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                'event_dispatcher' => EventDispatcherInterface::class,
            ];
    }

    /**
     * @Route("", name="login")
     *
     * @return Response
     */
    public function indexAction(AuthenticationUtils $authenticationUtils)
    {
        //check if auth failed last try
        if ($errorOccurred = (null !== $authenticationUtils->getLastAuthenticationError(true))) {
            $this->displayError($this->getTranslator()->trans('login.error.login_failed', [], 'login'));
        }

        // create login form
        $form = $this->createForm(LoginType::class);
        $form->add('form.login', SubmitType::class, ['translation_domain' => 'login', 'label' => 'login.do_login']);

        return $this->render('login/login.html.twig', ['form' => $form->createView(), 'error_occurred' => $errorOccurred]);
    }

    /**
     * @Route("/choose/{organisation}", name="login_choose")
     *
     * @return Response
     */
    public function chooseAction(Organisation $organisation, ClientInterface $client)
    {
        return $this->render('login/choose.html.twig', ['organisation' => $organisation, 'open_id_enabled' => $client->isEnabled()]);
    }

    /**
     * @Route("/external", name="login_external")
     *
     * @return Response
     */
    public function externalAction(Request $request, TranslatorInterface $translator, AuthenticationServiceInterface $authenticationService)
    {
        $organisation = new Organisation();
        $organisation->setCategory(OrganisationCategoryType::EXTERNAL);

        $myOnSuccessCallable = function () use ($organisation, $translator, $authenticationService) {
            $existing = $this->getDoctrine()->getRepository(Organisation::class)->findOneBy(['email' => $organisation->getEmail()]);
            if ($existing === null) {
                $organisation->generateAuthenticationCode();
                $this->fastSave($organisation);
            } else {
                $organisation = $existing;
            }

            return $this->redirectToRoute('login_request_code', ['organisation' => $organisation->getId()]);
        };

        $buttonLabel = $translator->trans('form.submit_buttons.create', [], 'framework');
        $formType = $this->createForm(ExternalOrganisationType::class, $organisation)
            ->add('submit', SubmitType::class, ['label' => $buttonLabel, 'translation_domain' => false]);
        $form = $this->handleForm($formType, $request, $myOnSuccessCallable);

        if ($form instanceof Response) {
            return $form;
        }

        return $this->render('login/external.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/open-id-connect", name="login_open_id_connect")
     *
     * @return Response
     */
    public function openIdConnectAction(ClientInterface $client)
    {
        $redirectUrl = $this->generateUrl('login_open_id_connect_response', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $state = 42;

        return $client->redirect($redirectUrl, $state);
    }

    /**
     * @Route("/open-id-connect/response", name="login_open_id_connect_response")
     *
     * @return Response
     */
    public function openIdConnectResponseAction(Request $request)
    {
        dump($request);

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/request_code/{organisation}", name="login_request_code")
     *
     * @return Response
     */
    public function requestCodeAction(Organisation $organisation, AuthenticationServiceInterface $authenticationService, TranslatorInterface $translator)
    {
        $authenticationCodeRequestTimeoutInDays = max(0, $this->getParameter('AUTHENTICATION_CODE_REQUEST_TIMEOUT_IN_DAYS'));
        $requestTimeout = new \DateTime();
        $requestTimeout->sub(new \DateInterval('P' . $authenticationCodeRequestTimeoutInDays . 'D'));
        if ($organisation->getLastAuthenticationCodeRequestAt() !== null && $organisation->getLastAuthenticationCodeRequestAt() > $requestTimeout) {
            $this->displayError($translator->trans('request_code.error.requested_too_often', ['%days%' => $authenticationCodeRequestTimeoutInDays], 'login'));
        } else {
            $organisation->generateAuthenticationCode();
            $organisation->setAuthenticationCodeRequestOccurred();
            $url = $this->generateUrl('login_code', ['code' => $organisation->getAuthenticationCode()], UrlGeneratorInterface::ABSOLUTE_URL);
            $authenticationService->sendAuthenticationCode($organisation, $url);
            $this->fastSave($organisation);

            $this->displaySuccess($translator->trans('request_code.success', [], 'login'));
        }

        return $this->redirectToRoute('login_choose', ['organisation' => $organisation->getId()]);
    }

    /**
     * @Route("/code/{code}", name="login_code")
     *
     * @return Response
     */
    public function codeAction(Request $request, string $code, UserProvider $provider)
    {
        /** @var Organisation $organisation */
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->findOneBy(['authenticationCode' => $code, 'hiddenAt' => null]);
        if ($organisation === null) {
            $this->displayError($this->getTranslator()->trans('login.error.invalid_auth_code', [], 'login'));
        } else {
            $user = $provider->loadUserByUsername($organisation->getEmail());
            $this->loginUser($request, $user);

            return $this->redirectToRoute('organisation_view', ['organisation' => $organisation->getId()]);
        }

        return $this->render('login/login_code.html.twig');
    }

    protected function loginUser(Request $request, UserInterface $user)
    {
        //login programmatically
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="login_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}

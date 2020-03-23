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
use App\Form\PasswordContainer\LoginType;
use App\Security\UserProvider;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

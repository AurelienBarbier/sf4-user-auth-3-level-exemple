<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Mailer\User as UserMessenger;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\UserAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {

            $this->addFlash('warning', 'you are already logged !');

            return $this->redirectToRoute('user');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="app_eistration")
     */
    public function register(UserAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler, Request $request, UserPasswordEncoderInterface $userPasswordEncoder, UserMessenger $messenger): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'you are already logged !');
            return $this->redirectToRoute('user');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $userPasswordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setToken(md5(random_bytes(30)));
            $em->persist($user);
            $em->flush();

            $messenger->sendAccountConfirmationMessage($user);

            $this->addFlash('warning', 'Please, check your mail box to to confirm your email adresse');
        }


        return $this->render('security/register.html.twig', ['form_registration' => $form->createView()]);

    }

    /**
     * @Route("/confirm", name="app_confirm")
     */
    public function accountConfirmation(Request $request, UserRepository $userRepository): Response
    {
        if ($request->query->has('token')) {
            $userRepository->activateToken($request->query->get('token'));
            $this->addFlash('success', 'Account activated with success. Please, login now !');
            $this->redirectToRoute('app_login');
        }

        throw new AuthenticationCredentialsNotFoundException('Your token is out of date');
    }

    /**
     * @Route("/reset-password/request", name="app_reset_password_request")
     */
    public function resetPasswordRequest(Request $request, UserMessenger $messenger, UserRepository $userRepository)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /**
             * @var User $user
             */
            $user = $userRepository->findOneByEmail($form->getData()['email']);
            if (!$user->isConfirmed()) {
                throw new CustomUserMessageAuthenticationException(
                    'Your account is not confirmed. Sorry about that but please check your mail box !'
                );
            }
            $user->setToken(md5(random_bytes(30)));
            $em->flush();
            $messenger->sendResetPasswordMessage($user);
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', ['form_reset_request' => $form->createView()]);

    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, User $user, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $form = $this->createFormBuilder($user)
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $userPasswordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setToken(null);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['form_reset' => $form->createView()]);
    }
}

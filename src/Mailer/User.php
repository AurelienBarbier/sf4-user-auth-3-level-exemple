<?php
/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 04/12/18
 * Time: 11:58
 */

namespace App\Mailer;

use App\Entity\User as AppUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig_Environment;

class User
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $from;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(\Swift_Mailer $mailer,
                                \Twig_Environment $twig,
                                TranslatorInterface $translator,
                                $from)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->from = $from;
    }

    public function sendAccountConfirmationMessage(AppUser $user)
    {
        $subject = $this->translator->trans('Registration confirmation');
        $message = (new \Swift_Message($subject))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render('emails/security/registration.html.twig', [
                        'subject' => $subject,
                        'user'    => $user,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }


    public function sendResetPasswordMessage(AppUser $user)
    {
        $subject = $this->translator->trans('Reset password request');
        $message = (new \Swift_Message($subject))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render('emails/security/reset_password.html.twig', [
                        'subject' => $subject,
                        'user'    => $user,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}

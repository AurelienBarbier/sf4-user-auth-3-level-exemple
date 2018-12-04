<?php
/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 04/12/18
 * Time: 11:58
 */

namespace App\Mailer;

use App\Entity\User as AppUser;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

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
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig , $from)
    {
        $this->mailer = $mailer;
        $this->from = $from;
        $this->twig = $twig;
    }

    public function sendAccountConfirmationMessage(AppUser $user)
    {

        $message = (new \Swift_Message('Registration confirmation'))
        ->setFrom($this->from)
        ->setTo($user->getEmail())
        ->setBody(
            $this->twig->render(
            // templates/emails/registration.html.twig
                'emails/security/registration.html.twig',
                array('user' => $user)
            ),
            'text/html'
        );

        $this->mailer->send($message);
    }


    public function sendResetPasswordMessage(AppUser $user)
    {

        $message = (new \Swift_Message('Reset password request'))
            ->setFrom($this->from)
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                // templates/emails/registration.html.twig
                    'emails/security/reset_password.html.twig',
                    array('user' => $user)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: aurelwcs
 * Date: 04/12/18
 * Time: 11:40
 */

namespace App\Security;


use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user is deleted, show a generic Account Not Found message.
        if (!$user->isConfirmed()) {
            throw new CustomUserMessageAuthenticationException(
                'Your account is not confirmed. Sorry about that but please check your mail box !'
            );
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }
    }
}
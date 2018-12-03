<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@site.com');
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'admin'
        ));
        $manager->persist($admin);

        $subscriber = new User();
        $subscriber->setEmail('subscriber@site.com');
        $subscriber->setRoles(array('ROLE_SUBSCRIBER'));
        $subscriber->setPassword($this->passwordEncoder->encodePassword(
            $subscriber,
            'subscriber'
        ));
        $manager->persist($subscriber);

        $manager->flush();
    }
}

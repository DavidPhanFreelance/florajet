<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('user1');
        $user->setEmail('utilisateur@test.com');

        $hashedPassword = password_hash('user1', PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $user->setIsEnabled(true);
        $user->setToken(bin2hex(openssl_random_pseudo_bytes(16)));

        $manager->persist($user);

        $manager->flush();
    }
}
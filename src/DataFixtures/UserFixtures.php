<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REFERENCE = 'user-admin';
    public const CLIENT_REFERENCE = 'user-client';

    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail($_ENV['ADMIN_EMAIL']); 
        $admin->setPasswordHash($this->hasher->hashPassword($admin, $_ENV['ADMIN_PASSWORD'])); 
        $admin->setAuthProvider('email');
        $admin->setPrenom($_ENV['ADMIN_PRENOM']); 
        $admin->setNom($_ENV['ADMIN_NOM']); 
        $admin->setPseudo('admin');
        $admin->setDob(new \DateTime($_ENV['ADMIN_DOB'])); 
        $admin->setRole('admin');
        $admin->setNotifications(true);
        $manager->persist($admin);
        $this->addReference(self::ADMIN_REFERENCE, $admin);

        $client = new User();
        $client->setEmail($_ENV['USER_EMAIL']);
        $client->setPasswordHash($this->hasher->hashPassword($client, $_ENV['USER_PASSWORD']));
        $client->setAuthProvider('email');
        $client->setPrenom($_ENV['USER_PRENOM']);
        $client->setNom($_ENV['USER_NOM']);
        $client->setPseudo($_ENV['USER_PSEUDO']);
        $client->setDob(new \DateTime($_ENV['USER_DOB']));
        $client->setRole('user');
        $client->setNotifications(true);
        $manager->persist($client);
        $this->addReference(self::CLIENT_REFERENCE, $client);

        $manager->flush();
    }
}
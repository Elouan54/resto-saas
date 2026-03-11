<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestUserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // OWNER
        $owner = new User();
        $owner->setEmail('test@owner.com');
        $owner->setRoles(['ROLE_OWNER']);
        $owner->setIsVerified(true);
        $owner->setCreatedAt(new \DateTime());

        $owner->setPassword(
            $this->passwordHasher->hashPassword($owner, 'test123')
        );

        $manager->persist($owner);

        // ADMIN
        $admin = new User();
        $admin->setEmail('test@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setCreatedAt(new \DateTime());

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'test123')
        );

        $manager->persist($admin);

        $manager->flush();
    }
}
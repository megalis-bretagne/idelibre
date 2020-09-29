<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    const REFERENCE = 'User_';

    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {

        /** @var Structure $structureLibriciel */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');

        /** @var Structure $structureMontpellier */
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        /** @var Group $groupRecia */
        $groupRecia = $this->getReference(GroupFixtures::REFERENCE . 'recia');

        /** @var Role $roleSuperAdmin */
        $roleSuperAdmin = $this->getReference(RoleFixtures::REFERENCE . 'superAdmin');

        /** @var Role $roleSuperAdmin */
        $roleGroupAdmin = $this->getReference(RoleFixtures::REFERENCE . 'groupAdmin');

        /** @var Role $roleSuperAdmin */
        $roleStructureAdminLibriciel = $this->getReference(RoleFixtures::REFERENCE . 'structureAdmin');

        ///////// SuperAdmin  ////////////////////

        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@example.org')
            ->setRole($roleSuperAdmin)
            ->setUsername('superadmin')
            ->setPassword($this->passwordEncoder->encodePassword($superAdmin, 'password'))
            ->setFirstName('super')
            ->setLastName('admin');

        $manager->persist($superAdmin);
        $this->addReference(self::REFERENCE . 'superadmin', $superAdmin);


        $otherSuperAdmin = new User();
        $otherSuperAdmin->setEmail('otherSuperadmin@example.org')
            ->setRole($roleSuperAdmin)
            ->setUsername('otherSuperadmin')
            ->setPassword($this->passwordEncoder->encodePassword($otherSuperAdmin, 'password'))
            ->setFirstName('otherSuper')
            ->setLastName('admin');

        $manager->persist($otherSuperAdmin);
        $this->addReference(self::REFERENCE . 'otherSuperadmin', $otherSuperAdmin);


        //////  user structure ///////

        $adminLibriciel = new User();
        $adminLibriciel->setEmail('userLibriciel@example.org')
            ->setRole($roleStructureAdminLibriciel)
            ->setUsername('admin@libriciel')
            ->setFirstName('admin')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($adminLibriciel, 'password'));

        $manager->persist($adminLibriciel);
        $this->addReference(self::REFERENCE . 'adminLibriciel', $adminLibriciel);


        $otherUserLibriciel = new User();
        $otherUserLibriciel->setEmail('otherUserLibriciel@example.org')
            ->setUsername('otherUser@libriciel')
            ->setFirstName('otherUser')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($otherUserLibriciel, 'password'));

        $manager->persist($otherUserLibriciel);
        $this->addReference(self::REFERENCE . 'otherUserLibriciel', $otherUserLibriciel);


        $userMontpellier = new User();
        $userMontpellier->setEmail('userMontpellier@example.org')
            ->setUsername('user@montpellier')
            ->setFirstName('user')
            ->setLastname('montpellier')
            ->setStructure($structureMontpellier)
            ->setPassword($this->passwordEncoder->encodePassword($userMontpellier, 'password'));

        $manager->persist($userMontpellier);
        $this->addReference(self::REFERENCE . 'userMontpellier', $userMontpellier);


        ////// admin group ////////////


        $userGroupRecia = new User();
        $userGroupRecia->setEmail('userGroupRecia@example.org')
            ->setRole($roleGroupAdmin)
            ->setUsername('userGroupRecia')
            ->setFirstName('userGroup')
            ->setLastname('Recia')
            ->setGroup($groupRecia)
            ->setPassword($this->passwordEncoder->encodePassword($userGroupRecia, 'password'));

        $manager->persist($userGroupRecia);
        $this->addReference(self::REFERENCE . 'userGroupRecia', $userGroupRecia);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            GroupFixtures::class,
            RoleFixtures::class
        ];
    }
}

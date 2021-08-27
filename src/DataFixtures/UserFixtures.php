<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Party;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use App\Security\Password\LegacyPassword;
use App\Service\Util\GenderConverter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'User_';

    private UserPasswordHasherInterface $passwordHasher;
    private LegacyPassword $legacyPassword;

    public function __construct(UserPasswordHasherInterface $passwordHasher, LegacyPassword $legacyPassword)
    {
        $this->passwordHasher = $passwordHasher;
        $this->legacyPassword = $legacyPassword;
    }

    public function load(ObjectManager $manager): void
    {
        /**
         * @var Structure $structureLibriciel
         * @var Structure $structureMontpellier
         * @var Group     $groupRecia
         * @var Group     $groupNotStructureCreator
         * @var Role      $roleSuperAdmin
         * @var Role      $roleGroupAdmin
         * @var Role      $roleStructureAdminLibriciel
         * @var Role      $roleActor
         * @var Role      $roleSecretary
         * @var Party     $partyMajority
         * @var Role      $roleGuest
         * @var Role      $roleEmployee
         */
        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        $groupRecia = $this->getReference(GroupFixtures::REFERENCE . 'recia');
        $groupNotStructureCreator = $this->getReference(GroupFixtures::REFERENCE . 'notStructureCreator');
        $roleSuperAdmin = $this->getReference(RoleFixtures::REFERENCE . 'superAdmin');
        $roleGroupAdmin = $this->getReference(RoleFixtures::REFERENCE . 'groupAdmin');
        $roleSecretary = $this->getReference(RoleFixtures::REFERENCE . 'secretary');
        $roleStructureAdminLibriciel = $this->getReference(RoleFixtures::REFERENCE . 'structureAdmin');
        $roleGuest = $this->getReference(RoleFixtures::REFERENCE . 'guest');
        $roleEmployee = $this->getReference(RoleFixtures::REFERENCE . 'employee');

        $roleActor = $this->getReference(RoleFixtures::REFERENCE . 'actor');
        $partyMajority = $this->getReference(PartyFixtures::REFERENCE . 'majorite');

        $password = $this->passwordHasher->hashPassword(new User(), 'password');

        ///////// SuperAdmin  ////////////////////

        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@example.org')
            ->setRole($roleSuperAdmin)
            ->setUsername('superadmin')
            ->setPassword($password)
            ->setFirstName('super')
            ->setLastName('admin');
        $manager->persist($superAdmin);
        $this->addReference(self::REFERENCE . 'superadmin', $superAdmin);

        $otherSuperAdmin = new User();
        $otherSuperAdmin->setEmail('otherSuperadmin@example.org')
            ->setRole($roleSuperAdmin)
            ->setUsername('otherSuperadmin')
            ->setPassword($password)
            ->setFirstName('otherSuper')
            ->setLastName('admin');
        $manager->persist($otherSuperAdmin);
        $this->addReference(self::REFERENCE . 'otherSuperadmin', $otherSuperAdmin);

        $superAdminInactive = new User();
        $superAdminInactive->setEmail('superadminInactive@example.org')
            ->setRole($roleSuperAdmin)
            ->setUsername('superadminInactive')
            ->setPassword($password)
            ->setFirstName('super')
            ->setLastName('admin inactive')
            ->setIsActive(false);
        $manager->persist($superAdminInactive);
        $this->addReference(self::REFERENCE . 'superadminInactive', $superAdminInactive);

        //////  user structure ///////

        $adminLibriciel = new User();
        $adminLibriciel->setEmail('userLibriciel@example.org')
            ->setRole($roleStructureAdminLibriciel)
            ->setUsername('admin@libriciel')
            ->setFirstName('admin')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($adminLibriciel);
        $this->addReference(self::REFERENCE . 'adminLibriciel', $adminLibriciel);

        $otherUserLibriciel = new User();
        $otherUserLibriciel->setEmail('otherUserLibriciel@example.org')
            ->setUsername('otherUser@libriciel')
            ->setFirstName('otherUser')
            ->setLastname('libriciel')
            ->setRole($roleActor)
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($otherUserLibriciel);
        $this->addReference(self::REFERENCE . 'otherUserLibriciel', $otherUserLibriciel);

        $userMontpellier = new User();
        $userMontpellier->setEmail('userMontpellier@example.org')
            ->setUsername('user@montpellier')
            ->setFirstName('user')
            ->setLastname('montpellier')
            ->setStructure($structureMontpellier)
            ->setPassword($password);
        $manager->persist($userMontpellier);
        $this->addReference(self::REFERENCE . 'userMontpellier', $userMontpellier);

        $userLegacy = new User();
        $userLegacy->setEmail('userLegacy@example.org')
            ->setUsername('userLegacy@montpellier')
            ->setFirstName('userLegacy')
            ->setLastname('montpellier')
            ->setRole($roleStructureAdminLibriciel)
            ->setStructure($structureMontpellier)
            ->setPassword($this->legacyPassword->encode('passwordLegacy'));
        $manager->persist($userLegacy);
        $this->addReference(self::REFERENCE . 'userLegacy', $userLegacy);

        ////// admin group ////////////

        $userGroupRecia = new User();
        $userGroupRecia->setEmail('userGroupRecia@example.org')
            ->setRole($roleGroupAdmin)
            ->setUsername('userGroupRecia')
            ->setFirstName('userGroup')
            ->setLastname('Recia')
            ->setGroup($groupRecia)
            ->setPassword($password);
        $manager->persist($userGroupRecia);
        $this->addReference(self::REFERENCE . 'userGroupRecia', $userGroupRecia);

        $adminNotStructureCreator = new User();
        $adminNotStructureCreator->setEmail('userGroupRecia@example.org')
            ->setRole($roleGroupAdmin)
            ->setUsername('adminNotStructureCreator')
            ->setFirstName('adminGroup')
            ->setLastname('NotStructureCreator')
            ->setGroup($groupNotStructureCreator)
            ->setPassword($password);
        $manager->persist($adminNotStructureCreator);
        $this->addReference(self::REFERENCE . 'adminNotStructureCreator', $adminNotStructureCreator);

        ///////// Actors  /////////////////////
        $actorLibriciel1 = new User();
        $actorLibriciel1->setEmail('actor1@example.org')
            ->setRole($roleActor)
            ->setUsername('actor1@libriciel')
            ->setFirstName('actor_1')
            ->setLastname('libriciel')
            ->setParty($partyMajority)
            ->setStructure($structureLibriciel)
            ->setGender(GenderConverter::MALE)
            ->setTitle('Madame le maire')
            ->setPassword($password);
        $manager->persist($actorLibriciel1);
        $this->addReference(self::REFERENCE . 'actorLibriciel1', $actorLibriciel1);

        $actorLibriciel2 = new User();
        $actorLibriciel2->setEmail('actor2@example.org')
            ->setRole($roleActor)
            ->setUsername('actor2@libriciel')
            ->setFirstName('actor_2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($actorLibriciel2);
        $this->addReference(self::REFERENCE . 'actorLibriciel2', $actorLibriciel2);

        $actorLibriciel3 = new User();
        $actorLibriciel3->setEmail('actor3@example.org')
            ->setRole($roleActor)
            ->setUsername('actor3@libriciel')
            ->setFirstName('actor_3')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($actorLibriciel3);
        $this->addReference(self::REFERENCE . 'actorLibriciel3', $actorLibriciel3);

        ////// Secretary ////

        $secretaryLibriciel1 = new User();
        $secretaryLibriciel1->setEmail('secretary1@example.org')
            ->setRole($roleSecretary)
            ->setUsername('secretary1@libriciel')
            ->setFirstName('secretary_1')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($secretaryLibriciel1);
        $this->addReference(self::REFERENCE . 'secretaryLibriciel1', $secretaryLibriciel1);

        $secretaryLibriciel2 = new User();
        $secretaryLibriciel2->setEmail('secretary2@example.org')
            ->setRole($roleSecretary)
            ->setUsername('secretary2@libriciel')
            ->setFirstName('secretary_2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($secretaryLibriciel2);
        $this->addReference(self::REFERENCE . 'secretaryLibriciel2', $secretaryLibriciel2);

        ///// GUEST //////

        $guestLibriciel1 = new User();
        $guestLibriciel1->setEmail('guest1@example.org')
            ->setRole($roleGuest)
            ->setUsername('guest1@libriciel')
            ->setFirstName('guest1')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($guestLibriciel1);
        $this->addReference(self::REFERENCE . 'guestLibriciel1', $guestLibriciel1);

        $guestLibriciel2 = new User();
        $guestLibriciel2->setEmail('guest2@example.org')
            ->setRole($roleGuest)
            ->setUsername('guest2@libriciel')
            ->setFirstName('guest2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($guestLibriciel2);
        $this->addReference(self::REFERENCE . 'guestLibriciel2', $guestLibriciel2);

        ///// EMPLOYEE //////

        $employeeLibriciel1 = new User();
        $employeeLibriciel1->setEmail('employee1@example.org')
            ->setRole($roleEmployee)
            ->setUsername('employee1@libriciel')
            ->setFirstName('employee1')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($employeeLibriciel1);
        $this->addReference(self::REFERENCE . 'employeeLibriciel1', $employeeLibriciel1);

        $employeeLibriciel2 = new User();
        $employeeLibriciel2->setEmail('employee2@example.org')
            ->setRole($roleEmployee)
            ->setUsername('employee2@libriciel')
            ->setFirstName('employee2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($password);
        $manager->persist($employeeLibriciel2);
        $this->addReference(self::REFERENCE . 'employeeLibriciel2', $employeeLibriciel2);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            StructureFixtures::class,
            GroupFixtures::class,
            RoleFixtures::class,
            PartyFixtures::class,
        ];
    }
}

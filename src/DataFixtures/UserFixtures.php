<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Party;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\User;
use App\Service\Util\GenderConverter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE = 'User_';

    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        /**
         * @var Structure $structureLibriciel
         * @var Structure $structureMontpellier
         * @var Group $groupRecia
         * @var Role $roleSuperAdmin
         * @var Role $roleGroupAdmin
         * @var Role $roleStructureAdminLibriciel
         * @var Role $roleActor
         * @var Role $roleSecretary
         * @var Party $partyMajority
         */

        $structureLibriciel = $this->getReference(StructureFixtures::REFERENCE . 'libriciel');
        $structureMontpellier = $this->getReference(StructureFixtures::REFERENCE . 'montpellier');

        $groupRecia = $this->getReference(GroupFixtures::REFERENCE . 'recia');
        $roleSuperAdmin = $this->getReference(RoleFixtures::REFERENCE . 'superAdmin');
        $roleGroupAdmin = $this->getReference(RoleFixtures::REFERENCE . 'groupAdmin');
        $roleSecretary = $this->getReference(RoleFixtures::REFERENCE . 'secretary');


        $roleStructureAdminLibriciel = $this->getReference(RoleFixtures::REFERENCE . 'structureAdmin');
        $roleActor = $this->getReference(RoleFixtures::REFERENCE . 'actor');
        $partyMajority = $this->getReference(PartyFixtures::REFERENCE . 'majorite');


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
            ->setRole($roleActor)
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

        ///////// Actors  /////////////////////
        $actorLibriciel1 = new User();
        $actorLibriciel1->setEmail('actor1@example.org')
            ->setRole($roleActor)
            ->setUsername('actor1@libriciel.coop')
            ->setFirstName('actor_1')
            ->setLastname('libriciel')
            ->setParty($partyMajority)
            ->setStructure($structureLibriciel)
            ->setGender(GenderConverter::MALE)
            ->setTitle('Madame le maire')
            ->setPassword($this->passwordEncoder->encodePassword($actorLibriciel1, 'password'));
        $manager->persist($actorLibriciel1);
        $this->addReference(self::REFERENCE . 'actorLibriciel1', $actorLibriciel1);

        $actorLibriciel2 = new User();
        $actorLibriciel2->setEmail('actor2@example.org')
            ->setRole($roleActor)
            ->setUsername('actor2@libriciel.coop')
            ->setFirstName('actor_2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($actorLibriciel2, 'password'));
        $manager->persist($actorLibriciel2);
        $this->addReference(self::REFERENCE . 'actorLibriciel2', $actorLibriciel2);

        $actorLibriciel3 = new User();
        $actorLibriciel3->setEmail('actor3@example.org')
            ->setRole($roleActor)
            ->setUsername('actor3@libriciel.coop')
            ->setFirstName('actor_3')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($actorLibriciel3, 'password'));
        $manager->persist($actorLibriciel3);
        $this->addReference(self::REFERENCE . 'actorLibriciel3', $actorLibriciel3);

        ////// Secretary ////
        $secretaryLibriciel1 = new User();
        $secretaryLibriciel1->setEmail('secretary1@example.org')
            ->setRole($roleSecretary)
            ->setUsername('secretary1@libriciel.coop')
            ->setFirstName('secretary_1')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($secretaryLibriciel1, 'password'));
        $manager->persist($secretaryLibriciel1);
        $this->addReference(self::REFERENCE . 'secretaryLibriciel1', $secretaryLibriciel1);


        $secretaryLibriciel2 = new User();
        $secretaryLibriciel2->setEmail('secretary2@example.org')
            ->setRole($roleSecretary)
            ->setUsername('secretary2@libriciel.coop')
            ->setFirstName('secretary_2')
            ->setLastname('libriciel')
            ->setStructure($structureLibriciel)
            ->setPassword($this->passwordEncoder->encodePassword($secretaryLibriciel2, 'password'));
        $manager->persist($secretaryLibriciel2);
        $this->addReference(self::REFERENCE . 'secretaryLibriciel2', $secretaryLibriciel2);

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

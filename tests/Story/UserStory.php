<?php

namespace App\Tests\Story;

use App\Service\Util\GenderConverter;
use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class UserStory extends Story
{
    // argon2i password
    public const PASSWORD = '$argon2id$v=19$m=65536,t=4,p=1$jCNjXFnpctIdKy2XKJ3d9w$B2THO9hICaf20D73R6PB0FDiR1+2RpJCZlpG6RExTlg';

    public function build(): void
    {
        ///////// SuperAdmin  ////////////////////
        $this->addState('superadmin', UserFactory::new([
            'username' => 'superadmin',
            'email' => 'superadmin@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'super',
            'lastName' => 'admin',
            'isActive' => true,
            'role' => RoleStory::superadmin(),
        ]));

        $this->addState('otherSuperadmin', UserFactory::new([
            'username' => 'otherSuperadmin',
            'email' => 'otherSuperadmin@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'otherSuper',
            'lastName' => 'admin',
            'isActive' => true,
            'role' => RoleStory::superadmin(),
        ]));

        $this->addState('superadminInactive', UserFactory::new([
            'username' => 'superadminInactive',
            'email' => 'superadminInactive@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'super',
            'lastName' => 'admin inactive',
            'isActive' => false,
            'role' => RoleStory::superadmin(),
        ]));

        $this->addState('adminLibriciel', UserFactory::new([
            'username' => 'admin@libriciel',
            'email' => 'userLibriciel@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'admin',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::admin(),
        ]));

        $this->addState('otherUserLibriciel', UserFactory::new([
            'username' => 'otherUser@libriciel',
            'email' => 'otherUserLibriciel@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'otherUser',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::actor(),
        ]));

        $this->addState('userMontpellier', UserFactory::new([
            'username' => 'user@montpellier',
            'email' => 'userMontpellier@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'user',
            'lastName' => 'montpellier',
            'structure' => StructureStory::montpellier(),
        ]));
        //
        //        $passwordLegacy = $this->legacyPassword->encode('passwordLegacy');
        //        $this->addState('userLegacy', UserFactory::new([
        //            'username' => 'userLegacy@montpellier',
        //            'email' => 'userLegacy@example.org',
        //            'password' => $passwordLegacy,
        //            'firstName' => 'userLegacy',
        //            'lastName' => 'montpellier',
        //            'structure' => StructureStory::montpellier(),
        //            'role' => RoleStory::admin()
        //        ]));

        // Admin de groupe
        $this->addState('userGroupRecia', UserFactory::new([
            'username' => 'userGroupRecia',
            'email' => 'userGroupRecia@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'userGroupRecia',
            'lastName' => 'Recia',
            'group' => GroupStory::recia(),
            'role' => RoleStory::groupadmin(),
        ]));

        $this->addState('adminNotStructureCreator', UserFactory::new([
            'username' => 'adminNotStructureCreator',
            'email' => 'userGroupRecia@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'adminGroup',
            'lastName' => 'NotStructureCreator',
            'group' => GroupStory::notStructureCreator(),
            'role' => RoleStory::groupadmin(),
        ]));

        // Actors
        $this->addState('actorLibriciel1', UserFactory::new([
            'username' => 'actor1@libriciel',
            'email' => 'actor1@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'actor_1',
            'lastName' => 'libriciel',
            'gender' => GenderConverter::MALE,
            'title' => 'Madame le maire',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::actor(),
            'party' => PartyStory::majorite(),
        ]));
        $this->addState('actorLibriciel2', UserFactory::new([
            'username' => 'actor2@libriciel',
            'email' => 'actor2@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'actor_2',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::actor(),
        ]));
        $this->addState('actorLibriciel3', UserFactory::new([
            'role' => RoleStory::actor(),
            'username' => 'actor3@libriciel',
            'firstName' => 'actor_3',
            'lastName' => 'libriciel',
            'email' => 'actor3@example.org',
            'password' => self::PASSWORD,
            'structure' => StructureStory::libriciel(),
        ]));

        $this->addState('mgille', UserFactory::new([
            'username' => 'm.gille@libriciel',
            'email' => 'm.gille@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'Maurice',
            'lastName' => 'Gille',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::actor(),
        ]));

        // Secrétaires
        $this->addState('secretaryLibriciel1', UserFactory::new([
            'username' => 'secretary1@libriciel',
            'email' => 'secretary1@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'secretary_1',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::secretary(),
        ]));
        $this->addState('secretaryLibriciel2', UserFactory::new([
            'username' => 'secretary2@libriciel',
            'email' => 'secretary2@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'secretary_2',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::secretary(),
        ]));

        // Guests
        $this->addState('guestLibriciel1', UserFactory::new([
            'username' => 'guest1@libriciel',
            'email' => 'guest1@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'guest1',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::guest(),
        ]));
        $this->addState('guestLibriciel2', UserFactory::new([
            'username' => 'guest2@libriciel',
            'email' => 'guest2@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'guest2',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::guest(),
        ]));

        // Employee
        $this->addState('employeeLibriciel1', UserFactory::new([
            'username' => 'employee1@libriciel',
            'email' => 'employee1@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'employee1',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::employee(),
        ]));
        $this->addState('employeeLibriciel2', UserFactory::new([
            'username' => 'employee2@libriciel',
            'email' => 'employee2@example.org',
            'password' => self::PASSWORD,
            'firstName' => 'employee2',
            'lastName' => 'libriciel',
            'structure' => StructureStory::libriciel(),
            'role' => RoleStory::employee(),
        ]));
    }
}

<?php

namespace App\Tests\Story;

use App\Tests\Factory\RoleFactory;
use Zenstruck\Foundry\Story;

final class RoleStory extends Story
{
    public function build(): void
    {
        $this->addState('superadmin', RoleFactory::new([
            'name' => 'SuperAdmin',
            'prettyName' => 'Super administrateur',
            'isInStructureRole' => false,
            'composites' => [
                'ROLE_SUPERADMIN',
                'ROLE_MANAGE_STRUCTURES',
                'ROLE_MANAGE_USERS'
            ]
        ]));

        $this->addState('groupadmin', RoleFactory::new([
            'name' => 'GroupAdmin',
            'prettyName' => 'Administrateur de groupe',
            'isInStructureRole' => false,
            'composites' => [
                'ROLE_GROUP_ADMIN',
                'ROLE_MANAGE_STRUCTURES',
                'ROLE_MANAGE_USERS'
            ]
        ]));

        $this->addState('admin', RoleFactory::new([
            'name' => 'Admin',
            'prettyName' => 'Administrateur',
            'isInStructureRole' => true,
            'composites' => [
                'ROLE_STRUCTURE_ADMIN',
                'ROLE_MANAGE_USERS'
            ]
        ]));
        $this->addState('secretary', RoleFactory::new([
            'name' => 'Secretary',
            'prettyName' => 'Gestionnaire de séance',
            'isInStructureRole' => true,
            'composites' => [
                'ROLE_SECRETARY'
            ]
        ]));
        $this->addState('actor', RoleFactory::new([
            'name' => 'Actor',
            'prettyName' => 'Elu',
            'isInStructureRole' => true,
            'composites' => [
                'ROLE_ACTOR'
            ]
        ]));
        $this->addState('guest', RoleFactory::new([
            'name' => 'Guest',
            'prettyName' => 'Invité',
            'isInStructureRole' => true,
            'composites' => [
                'ROLE_GUEST'
            ]
        ]));
        $this->addState('employee', RoleFactory::new([
            'name' => 'Employee',
            'prettyName' => 'Personnel administratif',
            'isInStructureRole' => true,
            'composites' => [
                'ROLE_EMPLOYEE'
            ]
        ]));

    }
}

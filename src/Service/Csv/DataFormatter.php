<?php

namespace App\Service\Csv;

use App\Entity\Enum\Role_Code;
use App\Entity\Role;
use App\Entity\Structure;
use App\Service\Enum\Csv_Records;
use App\Service\role\RoleManager;
use App\Service\Util\GenderConverter;
use ForceUTF8\Encoding;

class DataFormatter
{
    public function __construct(
        private readonly RoleManager $roleManager,
    ) {
    }

    public function formatUsername(string $username, Structure $structure): string
    {
        return $this->sanitize($username) . '@' . $structure->getSuffix();
    }

    public function getGenderCode(?int $code): ?int
    {
        if (null === $code) {
            return null;
        }

        if (in_array($code, [GenderConverter::NOT_DEFINED, GenderConverter::FEMALE, GenderConverter::MALE])) {
            return $code;
        }

        return null;
    }

    public function getRoleFromCode(int $roleId): ?Role
    {
        if (0 === $roleId) {
            return null;
        }

        return match ($roleId) {
            Role_Code::CODE_ROLE_SECRETARY => $this->roleManager->getSecretaryRole(),
            Role_Code::CODE_ROLE_STRUCTURE_ADMIN => $this->roleManager->getStructureAdminRole(),
            Role_Code::CODE_ROLE_ACTOR => $this->roleManager->getActorRole(),
            Role_Code::CODE_ROLE_EMPLOYEE => $this->roleManager->getEmployeeRole(),
            Role_Code::CODE_ROLE_GUEST => $this->roleManager->getGuestRole(),
            Role_Code::CODE_ROLE_DEPUTY => $this->roleManager->getDeputyRole(),
            default => null,
        };
    }

    public function sanitize(string $content): string
    {
        $trim_content = trim($content);
        // quick fix for non utf8 file
        return Encoding::toUTF8($trim_content);
    }

    public function sanitizePhoneNumber(string $phone): string
    {
        if (str_contains($phone, '.')) {
            return str_replace('.', '', $phone);
        }
        if (str_contains($phone, ' ')) {
            return str_replace(' ', '', $phone);
        }
        if (str_contains($phone, '-')) {
            return str_replace('-', '', $phone);
        }

        return $phone;
    }

    public function actorTitle(array $record): string
    {
        return $record[Csv_Records::ROLE->value] === strval(Role_Code::CODE_ROLE_ACTOR) ? $this->sanitize($record[Csv_Records::TITLE->value]) : '';
    }
}

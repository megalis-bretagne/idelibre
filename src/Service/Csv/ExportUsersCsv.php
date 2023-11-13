<?php

namespace App\Service\Csv;


use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

class ExportUsersCsv
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository
    )
    {
    }

    public function generate($structure): string
    {
        $csvPath = '/tmp/' . uniqid('csv_users');
        $writer = Writer::createFromPath($csvPath, 'w+');

        try {
            $writer->insertOne($this->getHeaders());
        } catch (CannotInsertRecord|Exception $e) {
        }

        $users = $this->userRepository->findByStructure($structure)->getQuery()->getResult();
        foreach ($users as $user) {

            try {
                $writer->insertOne($this->getUserData($user));
            } catch (CannotInsertRecord|Exception $e) {
            }
        }

        return $csvPath;
    }

    public function getHeaders(): array
    {
        return ['Nom', 'Prénom', 'Username', 'Email', 'Profil', 'Groupe Politique', 'Groupe' ,'Suppléant'];
    }

    private function getUserData($user)
    {



        return [
            $user->getLastName(),
            $user->getFirstName(),
            $user->getUsername(),
            $user->getEmail(),
            $this->formatUserRoles($user),
            "Groupe Politique",
            "Party",
            "Deputy",
//            $this->formatUserRoles($user->getRoles()),
//            $user->getParty() ? $user->getParty()->getname() :  "" , # partyId => findPartyById => getName
//            $user->getGroup() ? $user->getGroup()->getName() : "", # groupId => findGroupById => getName
//            $user->getDeputy() ? $user->getDeputy()->getFirstName() . " " . $user->getDeputy()->getLastName() : "",
        ];
    }

    private function formatUserRoles($user): string
    {

    }

}

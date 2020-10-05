<?php


namespace App\Service\Party;


use App\Entity\Party;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Iterable_;

class PartyManager
{

    private EntityManagerInterface $em;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function save(Party $party, Structure $structure)
    {
        $party->setStructure($structure);

        $this->em->persist($party);
        $this->em->flush();
    }


    public function update(Party $party, Structure $structure)
    {
        $party->setStructure($structure);

        $this->dissociateUsers($party->getActors()->toArray(), $party);
        $this->associateUsers($party->getActors()->toArray(), $party);

        $this->em->persist($party);
        $this->em->flush();
    }

    /**
     * @param User[] $selectedUsers
     *
     */
    private function dissociateUsers(array $selectedUsers, Party $party)
    {
        $associatedUsersInDB = $this->userRepository->findBy(['party' => $party]);
        foreach ($associatedUsersInDB as $associatedUserInDB) {
            if (!$this->isSelected($associatedUserInDB, $selectedUsers)) {
                $associatedUserInDB->setParty(null);
            }
        }
    }

    /**
     * @param User[] $selectedUsers
     */
    private function associateUsers(array $selectedUsers, Party $party) {
        foreach ($selectedUsers as $selectedUser) {
            $selectedUser->setParty($party);
        }
    }

    /**
     * @param User[] $selectedUsers
     */
    private function isSelected(User $userInDB, array $selectedUsers): bool
    {
        return in_array($userInDB, $selectedUsers);
    }


}

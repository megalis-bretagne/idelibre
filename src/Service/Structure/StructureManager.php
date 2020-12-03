<?php


namespace App\Service\Structure;

use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\LsmessageConnector;
use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Service\Connector\ComelusConnectorManager;
use App\Service\Connector\LsmessageConnectorManager;
use App\Service\role\RoleManager;
use App\Service\Theme\ThemeManager;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StructureManager
{
    // TODO SPLIT CLASS

    private StructureRepository $structureRepository;
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private ParameterBagInterface $bag;
    private ImpersonateStructure $impersonateStructure;
    private RoleManager $roleManager;
    private ThemeManager $themeManager;
    private ComelusConnectorManager $comelusConnectorManager;
    private LsmessageConnectorManager $lsmessageConnectorManager;


    public function __construct(
        StructureRepository $structureRepository,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        ParameterBagInterface $bag,
        RoleManager $roleManager,
        ImpersonateStructure $impersonateStructure,
        ThemeManager $themeManager,
        ComelusConnectorManager $comelusConnectorManager,
        LsmessageConnectorManager $lsmessageConnectorManager
    ) {
        $this->structureRepository = $structureRepository;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->bag = $bag;
        $this->impersonateStructure = $impersonateStructure;
        $this->roleManager = $roleManager;
        $this->themeManager = $themeManager;
        $this->comelusConnectorManager = $comelusConnectorManager;
        $this->lsmessageConnectorManager = $lsmessageConnectorManager;
    }

    public function save(Structure $structure): void
    {
        $this->em->persist($structure);
        $this->em->flush();
    }


    public function delete(Structure $structure): void
    {
        $this->impersonateStructure->logoutEverySuperAdmin($structure);
        
        $this->em->remove($structure);
        $this->em->flush();
    }

    /**
     * @throws ComelusConnectorException
     * @throws LsmessageConnectorException
     */
    public function create(Structure $structure, User $user, string $plainPassword, Group $group = null): ?ConstraintViolationListInterface
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $errors = $this->validator->validate($user);

        if (count($errors)) {
            return $errors;
        }

        $user->setRole($this->roleManager->getStructureAdminRole());
        $this->em->persist($user);
        $structure->addUser($user);

        $structure->setGroup($group);

        $this->em->persist($structure);
        $this->em->flush();

        $this->themeManager->createStructureRootNode($structure);

        $this->comelusConnectorManager->createConnector($structure);
        $this->lsmessageConnectorManager->createConnector($structure);

        return null;
    }

    public function replaceReplyTo(Structure $structure, ?string $replyTo): void
    {
        $structure->setReplyTo($replyTo);
        $this->em->persist($structure);
        $this->em->flush();
    }
}

<?php


namespace App\Service\Structure;

use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Repository\StructureRepository;
use App\Service\User\ImpersonateStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StructureManager
{
    private StructureRepository $structureRepository;
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;
    private ValidatorInterface $validator;
    private ParameterBagInterface $bag;
    //private $emailTemplateManager;
    private ImpersonateStructure $impersonateStructure;

    public function __construct(
        StructureRepository $structureRepository,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        ParameterBagInterface $bag,
        //EmailTemplateManager $emailTemplateManager,
        ImpersonateStructure $impersonateStructure
    ) {
        $this->structureRepository = $structureRepository;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->bag = $bag;
        //$this->emailTemplateManager = $emailTemplateManager;
        $this->impersonateStructure = $impersonateStructure;
    }

    public function save(Structure $structure)
    {
        $this->em->persist($structure);
        $this->em->flush();
    }


    public function delete(Structure $structure)
    {

        //deco superadmin and group admin
        $this->impersonateStructure->logoutEverySuperAdmin($structure);
        
        $this->em->remove($structure);

        $this->em->flush();
    }


    public function create(Structure $structure, User $user, string $plainPassword, Group $group = null): ?ConstraintViolationListInterface
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $errors = $this->validator->validate($user);

        if (count($errors)) {
            return $errors;
        }

        $this->em->persist($user);
        $structure->addUser($user);

        //$structure->setApiToken(new ApiToken($structure));

        $structure->setGroup($group);

        $this->em->persist($structure);
        $this->em->flush();

        //intialize default templates
        //$this->emailTemplateManager->initDefaultTemplates($structure);

        return null;
    }

    public function replaceReplyTo(Structure $structure, ?string $replyTo)
    {
        $structure->setReplyTo($replyTo);
        $this->em->persist($structure);
        $this->em->flush();
    }
}

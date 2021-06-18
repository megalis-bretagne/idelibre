<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TypeRepository;
use App\Service\role\RoleManager;
use App\Service\Seance\SittingManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SittingType extends AbstractType
{
    private TypeRepository $typeRepository;
    private SittingManager $sittingManager;
    private RoleManager $roleManager;

    public function __construct(TypeRepository $typeRepository, SittingManager $sittingManager, RoleManager $roleManager)
    {
        $this->typeRepository = $typeRepository;
        $this->sittingManager = $sittingManager;
        $this->roleManager = $roleManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !isset($options['data']);
        $isAlreadySentConvocation = !$isNew && $this->sittingManager->isAlreadySentConvocation($options['data']);
        $isAlreadySentInvitation = !$isNew && $this->sittingManager->isAlreadySentInvitation($options['data']);

        $builder
            ->add('type', EntityType::class, [
                'label' => 'Type de séance',
                'class' => Type::class,
                'query_builder' => $this->isSecretary($options['user'])
                    ? $this->typeRepository->findAuthorizedTypeByUser($options['user'])
                    : $this->typeRepository->findByStructure($options['structure']),
                'choice_label' => 'name',
                'disabled' => !$isNew,
                'constraints' => [new NotNull(null, 'le fichier de convocation est obligatoire')],
            ])
            ->add('date', null, [
                'label' => 'Date et heure',
                'required' => true,
                'widget' => 'single_text',
                'view_timezone' => $this->getTimeZone($options['structure']),
                'disabled' => $isAlreadySentConvocation || $isAlreadySentInvitation,
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
                'disabled' => $isAlreadySentConvocation || $isAlreadySentInvitation,
            ])
            ->add('convocationFile', LsFileType::class, [
                'label' => $isNew ? 'Fichier de convocation' : 'Remplacer le fichier de convocation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => $isNew,
                'file_name' => $this->getConvocationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentConvocation,
                'constraints' => $isNew ? [new NotNull(null, 'le fichier de convocation est obligatoire')] : [],
            ])
            ->add('invitationFile', LsFileType::class, [
                'label' => $isNew ? 'Fichier d\'invitation' : 'Remplacer le fichier d\'invitation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => false,
                'file_name' => $this->getInvitationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentInvitation,
            ])
            ->add('structure', HiddenType::class, [
                'data' => $options['structure'],
                'data_class' => null,
            ])
            ->get('structure')->addModelTransformer(new CallbackTransformer(
                fn () => '',
                fn () => $options['structure']
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sitting::class,
            'structure' => null,
            'user' => null,
        ]);
    }

    private function isSecretary(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->getRole()->getId() === $this->roleManager->getSecretaryRole()->getId();
    }

    private function getConvocationFileName(?Sitting $sitting): ?string
    {
        if (!$sitting) {
            return null;
        }
        if (!empty($sitting->getConvocationFile())) {
            return $sitting->getConvocationFile()->getName();
        }

        return null;
    }

    private function getInvitationFileName(?Sitting $sitting): ?string
    {
        if (!$sitting) {
            return null;
        }
        if (!empty($sitting->getInvitationFile())) {
            return $sitting->getInvitationFile()->getName();
        }

        return null;
    }

    private function getTimeZone(Structure $structure): string
    {
        return $structure->getTimezone()->getName();
    }
}

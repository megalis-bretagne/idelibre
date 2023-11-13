<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\LsChoiceType;
use App\Form\Type\LsFileType;
use App\Repository\TypeRepository;
use App\Service\role\RoleManager;
use App\Service\Seance\SittingManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
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
        /** @var Sitting|null $sitting */
        $sitting = $builder->getData();

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
                'constraints' => [new NotNull(null, 'Le type de séance est obligatoire')],
            ])
            ->add('date', null, [
                'label' => 'Date et heure',
                'required' => true,
                'widget' => 'single_text',
                'view_timezone' => $this->getTimeZone($options['structure']),
                'disabled' => $isAlreadySentConvocation || $isAlreadySentInvitation,
                'attr' => ['class' => ($isAlreadySentConvocation || $isAlreadySentInvitation) ? 'force-disabled-color' : ''],
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
                'disabled' => $isAlreadySentConvocation || $isAlreadySentInvitation,
            ])
            ->add('convocationFile', LsFileType::class, [
                'label' => ($isNew || $isAlreadySentConvocation) ? 'Fichier de convocation' : 'Remplacer le fichier de convocation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => $isNew,
                'file_name' => $this->getConvocationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentConvocation,
                'constraints' => $isNew ?
                    [
                        new NotNull(null, 'le fichier de convocation est obligatoire'),
                        new File([
                            'mimeTypes' => ['application/pdf'],
                            'mimeTypesMessage' => 'Le fichier doit être un pdf',
                        ]), ] :
                    [new File([
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Le fichier doit être un pdf',
                    ])],
            ])
            ->add('invitationFile', LsFileType::class, [
                'label' => ($isNew || $isAlreadySentInvitation) ? 'Fichier d\'invitation' : 'Remplacer le fichier d\'invitation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => false,
                'file_name' => $this->getInvitationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentInvitation,
                'constraints' => [new File([
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => 'Le fichier doit être un pdf',
                ])],
            ])

            ->add('reminder', ReminderSittingType::class, [
                'label' => false,
                'row_attr' => ["class" => $sitting ? "isDisabled" : ""],

          ])

            ->add('isRemoteAllowed', LsChoiceType::class, [
                'label' => ($isNew || $isAlreadySentConvocation) ? 'Participation à distance' : 'Autoriser la participation à distance',
                'data' => !$sitting || $sitting->getIsRemoteAllowed(),
                'attr' => [
                    'class' => $isAlreadySentConvocation || $isAlreadySentInvitation ? 'isDisabled' : '',
                    'data-infos' => $sitting ? $sitting->getId() : '',
                ],
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],

            ])

            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sitting::class,
            'structure' => null,
            'user' => null,
            'sitting' => null
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

    public function isRemoteAllowed($sitting): bool
    {
        return !$sitting ? false : $sitting->getIsRemoteAllowed();
    }
}

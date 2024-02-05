<?php

namespace App\Form;

use App\Entity\Party;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Form\Type\LsChoiceType;
use App\Repository\PartyRepository;
use App\Repository\RoleRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\role\RoleManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function __construct(
        private readonly RoleRepository  $roleRepository,
        private readonly PartyRepository $partyRepository,
        private readonly RoleManager     $roleManager,
        private readonly TypeRepository  $typeRepository,
        private readonly Security        $security,
        private readonly UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User|null $user */
        $user = $builder->getData();
        $isMySelf = ($this->security->getUser() === $user);



        $builder
            ->add('gender', ChoiceType::class, [
                'label' => 'Civilité',
                'choices' => [
                    'Madame' => 1,
                    'Monsieur' => 2,
                ],
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ]);


            $builder->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'disabled' => !$this->isNew($options),
                'attr' => [
                    'readonly' => !$this->isNew($options),
                ],
            ]) ;


        $builder->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone mobile (06XXXXXXXX ou 07XXXXXXXX) ',
                'required' => false,
            ])
            ->add('redirect_url', HiddenType::class, [
                'mapped' => false,
                'data' => $options['referer'],
            ]);

        if ($this->isNew($options)) {
            $builder->add('role', EntityType::class, [
                'required' => true,
                'label' => 'Profil',
                'class' => Role::class,
                'choice_label' => 'prettyName',
                'query_builder' => $this->roleRepository->findInStructureQueryBuilder(),
                'placeholder' => 'Sélectionnez une valeur',
                'attr' => [
                    'data-roleAdmin' => $this->roleManager->getAdminRole()->getId(),
                    'data-roleActor' => $this->roleManager->getActorRole()->getId(),
                ]
            ]);
        }

        if ($this->isNewOrActor($options)) {
            $builder
                ->add('title', TextType::class, [
                    'required' => false,
                    'label' => 'Titre',
                ])
                ->add('party', EntityType::class, [
                    'required' => false,
                    'label' => 'Groupe politique',
                    'class' => Party::class,
                    'query_builder' => $this->partyRepository->findByStructure($options['structure']),
                    'choice_label' => 'name',
                ])
                ->add('deputy', EntityType::class, [
                    'label' => 'Suppléant',
                    'row_attr' => ["class" => "d-none", "id" => "deputyGroup"],
                    'class' => User::class,
                    'choice_label' => fn (User $user) => $this->formatName($user),
                    'query_builder' => $this->userRepository->findDeputiesWithNoAssociation($options['structure'], $options['toExclude']),
                    'placeholder' => "--",
                    'required' => false,
                ])
            ;
        }


        if ($this->isExistingActor($options)) {
            $builder->add('deputy', EntityType::class, [
                'label' => 'Suppléant',
                'class' => User::class,
                'choice_label' => fn (User $user) => $this->formatName($user),
                'query_builder' => $this->userRepository->findDeputiesWithNoAssociation($options['structure'], $options['toExclude']),
                'data' => $options['data']->getDeputy() ? $options['data']->getDeputy() : null,

                'required' => false,
                //todo queryBuilder limitant aux deputy disponiblent de la structure.
            ]);
        }

        if ($this->IsSecretary($options)) {
            $builder->add('authorizedTypes', EntityType::class, [
                'required' => false,
                'label' => 'Types Autorisés',
                'class' => Type::class,
                'query_builder' => $this->typeRepository->findByStructure($options['structure']),
                'choice_label' => 'name',
                'multiple' => true,
                'by_reference' => false,
            ]);
        }

        if (false === $isMySelf) {
            $builder->add('isActive', LsChoiceType::class, [
                'label' => 'Actif',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$user || $user->getIsActive(),
            ]);
        }

        if (!$this->IsAdmin($options)) {
            $builder
                ->add('initPassword', LsChoiceType::class, [
                    'mapped' => false,
                    'label' => 'Voulez vous définir le mot de passe de l\'utilisateur ?',
                    'choices' => [
                        'Oui' => true,
                        'Non' => false,
                    ],
                    'required' => true,
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'mapped' => false,
                    'required' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe ne sont pas identiques',
                    'options' => [
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                        'attr' => [
                            'class' => 'password-field showValidationPasswordEntropy',
                            'data-minimum-entropy' => $options['entropyForUser'],
                            'autocomplete' => 'new-password',
                        ],
                    ],
                    'first_options' => [
                        'label' => 'Mot de passe',
                    ],
                    'second_options' => [
                        'label' => 'Confirmer',
                    ],
                ]);
        }

        $builder->get('username')->addModelTransformer(new CallbackTransformer(
            fn ($username) => $username ? preg_replace('/@.*/', '', $username) : '',
            fn ($username) => $username . '@' . $this->getStructureSuffix($options['structure'])
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'structure' => null,
            'entropyForUser' => null,
            'referer' => null,
            'sitting' => null,
            'toExclude' => null
        ]);
    }

    private function isNew(array $options): bool
    {
        if (!isset($options['data'])) {
            return true;
        }
        /** @var User $user */
        $user = $options['data'];

        return !$user->getId();
    }

    private function isNewOrActor(array $options): bool
    {
        if ($this->isNew($options)) {
            return true;
        }

        /** @var User $user */
        $user = $options['data'];

        return $user->getRole()->getId() === $this->roleManager->getActorRole()->getId();
    }


    private function isExistingActor(array $options): bool
    {
        if ($this->isNew($options)) {
            return false;
        }

        /** @var User $user */
        $user = $options['data'];

        return $user->getRole()->getId() === $this->roleManager->getActorRole()->getId();
    }

    private function IsSecretary(array $options): bool
    {
        if ($this->isNew($options)) {
            return false;
        }
        /** @var User $user */
        $user = $options['data'];

        return $user->getRole()->getId() === $this->roleManager->getSecretaryRole()->getId();
    }

    private function getStructureSuffix(Structure $structure): string
    {
        return $structure->getSuffix();
    }

    private function IsAdmin(array $options): bool
    {
        if ($this->isNew($options)) {
            return false;
        }
        /** @var User $user */
        $user = $options['data'];

        return $user->getRole()->getId() === $this->roleManager->getAdminRole()->getId();
    }

    private function formatName($user): string
    {
        return $user->getLastName() . ' ' . $user->getFirstname();
    }
}

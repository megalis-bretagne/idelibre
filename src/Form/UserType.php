<?php

namespace App\Form;

use App\Entity\Party;
use App\Entity\Role;
use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\PartyRepository;
use App\Repository\RoleRepository;
use App\Repository\TypeRepository;
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
        private readonly RoleRepository $roleRepository,
        private readonly PartyRepository $partyRepository,
        private readonly RoleManager $roleManager,
        private readonly TypeRepository $typeRepository,
        private readonly Security $security,
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
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone mobile (06XXXXXXXX ou 07XXXXXXXX) ',
                'required' => false,
                'constraints' => [
                    new Regex('/^0(6|7)\d{8}$/', 'Le numéro de téléphone doit être de la forme 06xxxxxxxx ou 07xxxxxxxx'),
                ],
            ])
            ->add('redirect_url', HiddenType::class, [
                'mapped' => false,
                'data' => $options['referer'],
            ])
        ;

        if ($this->isNew($options)) {
            $builder->add('role', EntityType::class, [
                'required' => true,
                'label' => 'Profil',
                'class' => Role::class,
                'choice_label' => 'prettyName',
                'query_builder' => $this->roleRepository->findInStructureQueryBuilder(),
                'placeholder' => 'Sélectionnez une valeur',
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

                ->add('isDeputy', ChoiceType::class, [
                    "label" => "Est suppléant",
                    "row_attr" => ['class' => "d-none", 'id' => 'isDeputyGroup'],
                    "choices" => [
                        "Non" => false,
                        "Oui" => true
                    ],
                ])

                ->add('mandator', EntityType::class, [
                    'label' => 'Associer un suppléant',
                    'label_attr' => ["id"=>"mandatorNameLabel"],
                    "row_attr" => ['class' => "d-none", 'id' => 'mandatorGroup'],
                    'required' => false,
                    'class' => User::class,
                    'choice_label' => 'lastname',
                    'placeholder' => 'Liste d\'association possible',
                ])
            ;
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
            $builder->add('isActive', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Actif',
            ]);
        }

        if (!$this->IsAdmin($options)) {
            $builder
                ->add('initPassword', ChoiceType::class, [
                    'mapped' => false,
                    'label' => 'Voulez vous définir le mot de passe de l\'utilisateur ?',
                    'choices' => [
                        'Non' => false,
                        'Oui' => true,
                    ],
                    'data' => false,
                    'required' => true,
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'mapped' => false,
                    'required' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe ne sont pas identiques',
                    'options' => [
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

    public function isActor(array $options): bool
    {
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
}

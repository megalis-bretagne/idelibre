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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    private RoleRepository $roleRepository;
    private PartyRepository $partyRepository;
    private RoleManager $roleManager;
    private TypeRepository $typeRepository;

    public function __construct(
        RoleRepository $roleRepository,
        PartyRepository $partyRepository,
        RoleManager $roleManager,
        TypeRepository $typeRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->partyRepository = $partyRepository;
        $this->roleManager = $roleManager;
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'label' => 'Email', ]);

        if ($this->isNew($options)) {
            $builder->add('role', EntityType::class, [
                'required' => true,
                'label' => 'Profil',
                'class' => Role::class,
                'choice_label' => 'prettyName',
                'query_builder' => $this->roleRepository->findInStructureQueryBuilder(),
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
            ]);
        }

        $builder->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passes ne sont pas identiques',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => $this->isNew($options),
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmer'],
        ]);

        $builder->add('isActive', CheckboxType::class, [
            'required' => false,
            'label_attr' => ['class' => 'switch-custom'],
            'label' => 'Actif',
        ]);

        $builder->get('username')->addModelTransformer(new CallbackTransformer(
            fn ($username) => preg_replace('/@.*/', '', $username),
            fn ($username) => $username . '@' . $this->getStructureSuffix($options['structure'])
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'structure' => null,
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
}

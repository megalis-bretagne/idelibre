<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('associatedActors', EntityType::class, [
                'placeholder' => 'Sélectionner les élus',
                'required' => false,
                'label' => 'Acteurs associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findActorsByStructure($options['structure']),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
                'data' => $this->userRepository->getAssociatedActorsWithType($options['data'] ?? null),
            ])

            ->add('associatedEmployees', EntityType::class, [
                'placeholder' => 'Sélectionner les personnels administratifs',
                'required' => false,
                'label' => 'Personnels administratifs associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findInvitableEmployeesByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedInvitableEmployeesWithType($options['data'] ?? null),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])

            ->add('associatedGuests', EntityType::class, [
                'placeholder' => 'Sélectionner les Invités',
                'required' => false,
                'label' => 'invités associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findGuestsByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedGuestWithType($options['data'] ?? null),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])

            ->add('authorizedSecretaries', EntityType::class, [
                'placeholder' => 'Sélectionner les gestionnaires de séance autorisés',
                'required' => false,
                'label' => 'Gestionnaire de séance autorisés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findSecretariesByStructure($options['structure']),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
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

    private function formatUserString(User $user): string
    {
        return sprintf('%s %s <%s>', $user->getFirstName(), $user->getLastName(), $user->getUsername());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
            'structure' => null,
        ]);
    }
}

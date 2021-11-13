<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                'label' => 'Elus associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findActorsByStructure($options['structure']),
                'choice_label' => fn(User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
                'data' => $this->userRepository->getAssociatedActorsWithType($options['data'] ?? null),
            ])
            ->add('associatedEmployees', EntityType::class, [
                'placeholder' => 'Sélectionner les personnels administratifs',
                'required' => false,
                'label' => 'Personnels administratifs, Administrateurs, Gestionnaires de séance associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findInvitableEmployeesByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedInvitableEmployeesWithType($options['data'] ?? null),
                'choice_label' => fn(User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('associatedGuests', EntityType::class, [
                'placeholder' => 'Sélectionner les Invités',
                'required' => false,
                'label' => 'Invités associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findGuestsByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedGuestWithType($options['data'] ?? null),
                'choice_label' => fn(User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('isComelus', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Envoyer le dossier via comelus',
            ])
            ->add('isSms', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Notifier les élus via sms',
            ])
            ->add('authorizedSecretaries', EntityType::class, [
                'placeholder' => 'Sélectionner les gestionnaires de séance autorisés',
                'required' => false,
                'label' => 'Gestionnaires de séance autorisés à gérer la séance',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findSecretariesByStructure($options['structure']),
                'choice_label' => fn(User $user) => $this->formatUserString($user),
                'multiple' => true,
            ])
            ->add('reminder', ReminderType::class, [
                'label' => false,
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class
            ]);
    }

    private function formatUserString(User $user): string
    {
        $usernameWithoutSuffix = preg_replace('/@.*/', '', $user->getUsername());

        return sprintf('%s %s <%s>', $user->getFirstName(), $user->getLastName(), $usernameWithoutSuffix);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
            'structure' => null,
        ]);
    }
}

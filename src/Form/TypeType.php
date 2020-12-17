<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            ->add('name')
            ->add('associatedUsers', EntityType::class, [
                'placeholder' => 'Sélectionner les acteurs',
                'required' => false,
                'label' => 'Acteurs associés',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findActorByStructure($options['structure']),
                'choice_label' => function (User $user) {
                    return sprintf('%s %s <%s>', $user->getFirstName(), $user->getLastName(), $user->getUsername());
                },
                'multiple' => true,
            ])
            ->add('authorizedSecretaries', EntityType::class, [
                'placeholder' => 'Sélectionner les secretaires autorisées',
                'required' => false,
                'label' => 'Secretaires autorisées',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findSecretariesByStructure($options['structure']),
                'choice_label' => function (User $user) {
                    return sprintf('%s %s <%s>', $user->getFirstName(), $user->getLastName(), $user->getUsername());
                },
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
            'structure' => null,
        ]);
    }
}

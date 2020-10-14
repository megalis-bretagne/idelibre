<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddActorType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notAssociatedActors', EntityType::class, [
                'label' => 'Ajouter des acteurs',
                'class' => User::class,
                'choice_label'=> fn(User $user) => $user->getFirstName() . " " . $user->getLastName(),
                'query_builder' => $this->userRepository->findActorsNotInSitting($options['sitting'], $options['structure']),
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'sitting' => null,
            'structure' => null
        ]);
    }
}

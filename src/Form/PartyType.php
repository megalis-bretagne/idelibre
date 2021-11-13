<?php

namespace App\Form;

use App\Entity\Party;
use App\Entity\Structure;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyType extends AbstractType
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
            ->add('actors', EntityType::class, [
                'label' => 'Elus associés',
                'required' => false,
                'class' => User::class,
                'choice_label' => fn (User $user) => $user->getFirstName() . ' ' . $user->getLastName(),
                'multiple' => true,
                'query_builder' => $this->userRepository->findActorsByStructure($options['structure']),
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Party::class,
            'structure' => null,
        ]);
    }
}

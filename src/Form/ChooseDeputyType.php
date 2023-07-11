<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseDeputyType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('associatedWith', EntityType::class, [
                'label' => 'Suppléant',
                'class' => User::class,
                'choice_label' => 'lastname',
                'query_builder' => $this->userRepository->findAvailableDeputiesInStructure(
                    $options['structure'],
                    $options['toExcludes']
                ),
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'structure' => null,
            'toExcludes' => [],
        ]);
    }
}

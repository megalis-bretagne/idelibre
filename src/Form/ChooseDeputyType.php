<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseDeputyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('password')
            ->add('firstName')
            ->add('lastName')
            ->add('title')
            ->add('gender')
            ->add('isActive')
            ->add('phone')
            ->add('jwtInvalidBefore')
            ->add('isDeputy')
            ->add('mandatorType')
            ->add('structure')
            ->add('group')
            ->add('role')
            ->add('associatedTypes')
            ->add('party')
            ->add('authorizedTypes')
            ->add('subscription')
            ->add('mandator')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom', ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur', ])
            ->add('email', EmailType::class, [
                'label' => 'Email', ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passes ne sont pas identiques',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => !$options['isEditMode'],
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
            ]);

        if ($options['isGroupChoice']) {
            $builder->add('group', EntityType::class, [
                'label' => 'Groupe',
                'class' => Group::class,
                'choice_label' => 'name',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isEditMode' => false,
            'isGroupChoice' => false,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Service\role\RoleManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class SuperUserType extends AbstractType
{
    public function __construct(private RoleManager $roleManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom de l\'administrateur',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de l\'administrateur',])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur (sans @suffixe)',
                'constraints' => [
                    new Regex('/^((?!@).)*$/', 'le champ ne doit pas contenir le suffixe'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne sont pas identiques',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => !$options['isEditMode'],
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
            ])
            ->add('role', HiddenEntityType::class, [
                'data' => $this->roleManager->getSuperAdminRole(),
                'class_name' => Role::class
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

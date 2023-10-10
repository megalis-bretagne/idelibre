<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Service\role\RoleManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class SuperUserType extends AbstractType
{
    public function __construct(private readonly RoleManager $roleManager, private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User|null $user */
        $user = $builder->getData();
        $isNew = (!$user || null === $user->getId());
        $isMySelf = ($this->security->getUser() === $user);

        $disable = false;
        if (false === $isNew) {
            if (false === $isMySelf) {
                $disable = true;
            }
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom de l\'administrateur',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de l\'administrateur',
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur (sans @suffixe)',
                'constraints' => [
                    new Regex('/^((?!@).)*$/', 'le champ ne doit pas contenir le suffixe'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
//                'disabled' => $disable,
            ])
            ->add('role', HiddenEntityType::class, [
                'data' => $this->roleManager->getSuperAdminRole(),
                'class_name' => Role::class,
            ])
        ;

        if ($options['isGroupChoice']) {
            $builder->add('group', EntityType::class, [
                'label' => 'Groupe',
                'class' => Group::class,
                'choice_label' => 'name',
            ]);
        }

        if (false === $isMySelf) {
            $builder->add('isActive', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'checkbox-inline checkbox-switch'],
                'label' => 'Actif',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isEditMode' => false,
            'isGroupChoice' => false,
            'entropyForUser' => null,
        ]);
    }
}

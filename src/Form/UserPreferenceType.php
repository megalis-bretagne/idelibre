<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserPreferenceType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $options['data'];

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'disabled' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne sont pas identiques',
                'options' => [
                    'attr' => [
                        'class' => 'password-field showValidationPasswordEntropy',
                        'data-minimum-entropy' => $options['entropyForUser'],
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmer',
                ],
            ])
        ;

        $builder->get('username')->addModelTransformer(new CallbackTransformer(
            fn ($username) => preg_replace('/@.*/', '', $username),
            fn ($username) => $username . $this->getStructureSuffix($user->getStructure())
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'entropyForUser' => null,
        ]);
    }

    private function getStructureSuffix(?Structure $structure): string
    {
        if ($this->security->isGranted('ROLE_MANAGE_STRUCTURES')) {
            return '';
        }

        return '@' . $structure->getSuffix();
    }
}

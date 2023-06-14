<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Timezone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class StructureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Structure|null $entity */
        $entity = $builder->getData();
        $isNew = !$entity || null === $entity->getId();

        $builder->add('name', TextType::class, [
            'label' => 'Dénomination',
        ]);

        if ($isNew) {
            $builder->add('suffix', TextType::class, [
                'label' => 'Suffixe',
                'constraints' => [
                    new Regex('/^((?!@).)*$/', 'le champ ne doit pas contenir d\'@ ni de caractères spéciaux'),
                ],
            ]);
        }

        $builder->add('replyTo', TextType::class, [
            'label' => 'Email de réponse',
        ])
            ->add('can_edit_reply_to', CheckboxType::class, [
                'label' => 'Autorisation pour les administrateurs de modifier l\'email de réponse ?',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ])
            ->add('siren', TextType::class, [
                'label' => 'Numéro de siren',
                'required' => false,
            ])
            ->add('timezone', EntityType::class, [
                'class' => Timezone::class,
                'choice_label' => 'name',
                'multiple' => false,
                'label' => 'Fuseau horaire',
            ]);

        if (!$isNew) {
            $builder->add('suffix', TextType::class, [
                'label' => 'Suffixe',
                'disabled' => true,
            ])
                ->add('legacyConnectionName', TextType::class, [
                    'label' => 'Connexion',
                    'disabled' => true,
                ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ]);
        }

        if ($isNew) {
            $builder->add('user', SuperUserType::class, [
                'mapped' => false,
                'label' => false,
                'entropyForUser' => $options['entropyForUser'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
            'entropyForUser' => null,
        ]);
    }
}

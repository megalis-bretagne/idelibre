<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Timezone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Structure | null $entity */
        $entity = $builder->getData();
        $isNew = !$entity || null === $entity->getId();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Dénomination',
            ])

            ->add('replyTo', TextType::class, [
                'label' => 'Email de réponse',
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
            ])
        ;

        if (!$isNew) {
            $builder->add('suffix', TextType::class, [
                'label' => 'Suffixe',
                'disabled' => true
            ])
            ->add('legacyConnectionName', TextType::class, [
                'label' => 'Connexion',
                'disabled' => true
            ]);
        }


        if ($isNew) {
            $builder->add('user', SuperUserType::class, [
                'mapped' => false,
                'label' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Timezone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Dénomination',
            ])
            ->add('replyTo', TextType::class, [
                'label' => 'Email de réponse',
                'disabled' => true,
            ])
            ->add('timezone', EntityType::class, [
                'class' => Timezone::class,
                'choice_label' => 'name',
                'multiple' => false,
                'label' => 'Fuseau horaire',
                'disabled' => true,
            ])
            ->add('suffix', TextType::class, [
                'label' => 'Suffixe de connexion',
                'disabled' => true,
                'required' => false,
            ])
            ->add('legacyConnectionName', TextType::class, [
                'label' => 'Connexion',
                'disabled' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
            'disabled' => false,
        ]);
    }
}

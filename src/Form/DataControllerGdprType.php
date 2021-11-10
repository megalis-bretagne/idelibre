<?php

namespace App\Form;

use App\Entity\Gdpr\DataControllerGdpr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataControllerGdprType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Raison sociale',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Numéro de téléphone',
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
            ])
            ->add('representative', TextType::class, [
                'label' => 'Représentée par',
            ])
            ->add('quality', TextType::class, [
                'label' => 'en qualité de',
            ])
            ->add('siret', TextType::class, [
                'label' => 'Siret',
            ])
            ->add('ape', TextType::class, [
                'label' => 'Code ape',
            ])
            ->add('dpoName', TextType::class, [
                'label' => 'Identité du DPO',
            ])
            ->add('dpoEmail', TextType::class, [
                'label' => 'Adresse email du DPO',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DataControllerGdpr::class,
        ]);
    }
}

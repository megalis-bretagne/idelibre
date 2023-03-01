<?php

namespace App\Form;

use App\Entity\Configuration;
use App\Entity\Structure;
use App\Form\Type\HiddenEntityType;
use App\Service\Util\SuppressionDelayFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isSharedAnnotation', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Autoriser le partage d\'annotation entre élus',
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ])
            ->add('sittingSuppressionDelay', ChoiceType::class, [
                'required' => false,
                'label' => 'Temps après lequel une séance est supprimée',
                'label_attr' => ['class' => 'form-select'],

                'choices' => SuppressionDelayFormatter::DELAYS,
                'placeholder' => '-- Choisir une durée --',
            ])
            ->add('minimumEntropy', IntegerType::class, [
                'label' => 'Force du mot de passe minimum pour les utilisateurs de la structure (hors administrateur de groupe)',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
            'structure' => null,
        ]);
    }
}

<?php

namespace App\Form\Connector;

use App\Entity\Connector\LsvoteConnector;
use App\Entity\Structure;
use App\Form\Type\HiddenEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LsvoteConnectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("url", TextType::class, [
                "label" => "Url",
                "required" => true
            ])
            ->add("apiKey", TextType::class, [
                "label" => "Clé d'api",
                "required" => true
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Activer',
            ])
            ->add("structure", HiddenEntityType::class, [
                "data" => $options['structure'],
                "class_name" => Structure::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => LsvoteConnector::class,
            "structure" => null
        ]);
    }
}

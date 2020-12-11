<?php

namespace App\Form\Connector;

use App\Entity\Connector\ComelusConnector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ComelusConnectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, [
                'required' => false,
                'label' => 'Url',
                'constraints' => [new Length(['max' => ComelusConnector::MAX_URL_LENGTH])],
            ])
            ->add('apiKey', TextType::class, [
                'required' => false,
                'label' => 'Clé d\'api',
                'constraints' => [new Length(['max' => ComelusConnector::MAX_API_KEY_LENGTH])],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Texte d\'accompagnement',
                'attr' => [
                    'rows' => 5,
                ],
                'constraints' => [new Length(['max' => ComelusConnector::MAX_DESCRIPTION_LENGTH])],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Activer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ComelusConnector::class,
        ]);
    }
}

<?php

namespace App\Form\Connector;

use App\Entity\Connector\LsmessageConnector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class LsmessageConnectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, [
                'required' => false,
                'label' => 'Url',
                'constraints' => [new Length(['max' => LsmessageConnector::MAX_URL_LENGTH])]
            ])
            ->add('apiKey', TextType::class, [
                'required' => false,
                'label' => 'Clé d\'api',
                'constraints' => [new Length(['max' => LsmessageConnector::MAX_API_KEY_LENGTH])]
            ])
            ->add('sender', TextType::class, [
                'required' => false,
                'label' => 'Expéditeur',
                'constraints' => [new Length(['max' => LsmessageConnector::MAX_SENDER_LENGTH])]
            ])
            ->add('content', TextareaType::class, [
                'required' => false,
                'label' => 'Contenu du message',
                'attr' => [
                    'rows' => 3
                ],
                'constraints' => [new Length(['max' => LsmessageConnector::MAX_CONTENT_LENGTH])]
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Activer'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LsmessageConnector::class,
        ]);
    }
}

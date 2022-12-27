<?php

namespace App\Form;

use App\Form\Type\LsFileType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class CsvType extends AbstractType
{
    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $csvMaxSize = $this->bag->get('csv_max_size');

        $builder
            ->add('csv', LsFileType::class, [
                'label' => 'Fichier csv',
                'required' => true,
                'multiple' => false,
                'attr' => [
                    'placeholder' => 'Sélectionner un csv',
//                    'accept' => '.csv',
                ],
                'constraints' => [
                    new NotNull(null, 'Le fichier est obligatoire'),
                    new File([
                        'mimeTypes' => $this->bag->get('csv_mime_types_authorized'),
                        'mimeTypesMessage' => "Le fichier doit être au format '.csv'",
                        'maxSize' => $csvMaxSize,
                        'maxSizeMessage' => sprintf('Le fichier doit faire moins de %s', $csvMaxSize),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

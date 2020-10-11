<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SittingType extends AbstractType
{

    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, [
                'label' => 'type de séance',
                'class'=> Type::class,
                'query_builder' => $this->typeRepository->findByStructure($options['structure']),
                'choice_label'=> 'name'
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'required' => true
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required'=> false
            ])
            ->add('convocationFile', FileType::class, [
                'label' => 'convocation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sitting::class,
            'structure' => null
        ]);
    }
}

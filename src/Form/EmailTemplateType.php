<?php

namespace App\Form;

use App\Entity\EmailTemplate;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé'
            ])
            ->add('type', EntityType::class, [
                'label' => 'Type de séance',
                'placeholder' => 'Sélectionner un type',
                'required' => false,
                'class' => Type::class,
                'query_builder' => $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure($options['structure'], $options['data'] ?? null),
                'choice_label' => 'name'
            ])
            ->add('subject', TextType::class, [
                'label' => 'Objet'
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 15
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
            'structure' => null
        ]);
    }
}

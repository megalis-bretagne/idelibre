<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Structure;
use App\Repository\StructureRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupStructureType extends AbstractType
{
    private StructureRepository $structureRepository;

    public function __construct(StructureRepository $structureRepository)
    {
        $this->structureRepository = $structureRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('structures', EntityType::class, [
                'class' => Structure::class,
                'multiple' => true,
                'required' => false,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'isNew' => false,
        ]);
    }
}

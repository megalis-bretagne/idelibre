<?php

namespace App\Form\Type;

use App\Form\DataTransformer\HiddenEntityTransformer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class HiddenEntityType extends HiddenType
{

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'class_name' => null,
            'data_class' => null
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $modelTransformer = new HiddenEntityTransformer($options['class_name'], $this->managerRegistry);
        $builder->addModelTransformer($modelTransformer);
    }
}

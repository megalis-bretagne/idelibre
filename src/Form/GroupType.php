<?php

namespace App\Form;

use App\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('isStructureCreator', CheckboxType::class , [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Autoriser la création de structures',
            ])
        ;

        if ($options['isNew']) {
            $builder->add('user', SuperUserType::class, [
                'mapped' => false,
                'label' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'isNew' => false,
        ]);
    }
}

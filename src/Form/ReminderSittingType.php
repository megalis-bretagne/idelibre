<?php

namespace App\Form;

use App\Entity\Reminder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderSittingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


            $builder
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom'],
                    'label' => 'Ajouter au calendrier',
                ])
                ->add('duration', ChoiceType::class, [
                    'label' => 'Durée',
                    'choices' => Reminder::VALUES,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reminder::class,
            'sittingData' => null
        ]);
    }
}

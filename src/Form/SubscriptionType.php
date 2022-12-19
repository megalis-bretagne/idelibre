<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acceptMailRecap', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Accepter de recevoir des mails récaptulatifs des présences/absences des élus ?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user' => null,
        ]);
    }
}
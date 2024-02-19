<?php

namespace App\Form;

use App\Entity\Subscription;
use App\Form\Type\LsChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acceptMailRecap', LsChoiceType::class, [
                'label' => 'Voulez-vous recevoir des mails récaptulatifs des présences/absences des élus ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}

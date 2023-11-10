<?php

namespace App\Form;

use App\Entity\Reminder;
use App\Form\Type\LsChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Reminder|null $reminder */
        $reminder = $builder->getData();

        $builder
            ->add('isActive', LsChoiceType::class, [
            'label' => 'Ajouter au calendrier',
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'data' => false,
        ])

            ->add('duration', ChoiceType::class, [
                'label' => 'Durée',
                'choices' => Reminder::VALUES,
                'disabled' => true
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reminder::class,
        ]);
    }

    private function isActive(?Reminder $reminder): bool
    {
        if (!$reminder) {
            return false;
        }
        return $reminder->getIsActive();
    }
}

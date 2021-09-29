<?php

namespace App\Form;

use App\Entity\Calendar;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarSittingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Calendar|null $calendar */
            $calendar = $event->getData();
            $form = $event->getForm();

            $form
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom'],
                    'label' => 'Ajouter au calendrier',
                    'data' => $calendar ? $calendar->getIsActive() : false,
                ])
                ->add('duration', ChoiceType::class, [
                    'label' => 'Durée',
                    'disabled' => !$this->isActive($calendar),
                    'choices' => Calendar::VALUES,
                    'data' => $calendar ? $calendar->getDuration() : 120,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Calendar::class,
        ]);
    }

    private function isActive(?Calendar $calendar): bool
    {
        if ($calendar) {
            return $calendar->getIsActive();
        }

        return false;
    }
}

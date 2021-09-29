<?php

namespace App\Form;

use App\Entity\Reminder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderSittingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Reminder|null $reminder */
            $reminder = $event->getData();
            $form = $event->getForm();

            $form
                ->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom'],
                    'label' => 'Ajouter au calendrier',
                    'data' => $reminder ? $reminder->getIsActive() : false,
                ])
                ->add('duration', ChoiceType::class, [
                    'label' => 'Durée',
                    'disabled' => !$this->isActive($reminder),
                    'choices' => Reminder::VALUES,
                    'data' => $reminder ? $reminder->getDuration() : 120,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reminder::class,
        ]);
    }

    private function isActive(?Reminder $reminder): bool
    {
        if ($reminder) {
            return $reminder->getIsActive();
        }

        return false;
    }
}

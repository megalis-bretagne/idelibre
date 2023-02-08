<?php

namespace App\Form;

use App\Entity\Convocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('attendance', ChoiceType::class, [
                'required' => true,
                'label' => 'Merci de confirmer votre présence',
                'choices' => $this->getAttendanceValues($options['isRemoteAllowed'] ?? null),
                'empty_data' => Convocation::PRESENT,
            ])

            ->add('deputy', TextType::class, [
                'label' => 'Mandataire',
                'required' => false,
            ])
            ;
    }

    private function getAttendanceValues(?bool $isRemoteAllowed): array
    {
        $values = [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
        ];

        if ($isRemoteAllowed) {
            $values = [
                'Présent' => Convocation::PRESENT,
                'Présent à distance' => Convocation::REMOTE,
                'Absent' => Convocation::ABSENT,
            ];
        }

        return $values;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'isRemoteAllowed' => false,
        ]);
    }
}

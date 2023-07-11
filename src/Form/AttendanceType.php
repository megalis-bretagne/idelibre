<?php

namespace App\Form;

use App\Entity\Convocation;
use App\Entity\Structure;
use App\Form\Type\HiddenEntityType;
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
                'row_attr' => ["id" => "attendanceGroup"],
                'choices' => $this->getAttendanceValues($options['convocation'], $options['isRemoteAllowed'] ?? null),
                'empty_data' => Convocation::PRESENT,
            ])

            ->add('status', ChoiceType::class, [
                "mapped" => false,
                "label" => 'Remplacement',
                'choices' => [
                    "Non remplacé" => "none",
                    "Envoyer votre suppléant" => "deputy",
                    "Donner procuration" => "poa"
                ],
                "row_attr" => ["id" => "attendanceStatusGroup", "class" => "d-none"],
            ])

            ->add('deputy', ChoiceType::class, [
                'label' => 'Élu qui reçoit le pouvoir',
                'row_attr' => ["id" => "deputyGroup"],
                'required' => true,
            ])
        ;
    }

    private function getAttendanceValues(Convocation $convocation, ?bool $isRemoteAllowed): array
    {
        $values = [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
        ];

        if ($isRemoteAllowed && Convocation::CATEGORY_CONVOCATION === $convocation->getCategory()) {
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
            'convocation' => null,
        ]);
    }
}

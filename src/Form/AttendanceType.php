<?php

namespace App\Form;

use App\Entity\Convocation;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceType extends AbstractType
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

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

            ->add('mandataire', EntityType::class, [
                'label' => 'Mandataire',
                'row_attr' => ["id" => "attendance_mandataire_group", "class" => 'd-none'],
                'required' => true,
                'class' => User::class,
                'query_builder' => $this->userRepository->findActorsInSittingWithExclusion($options['sitting'], $options['toExclude']),
                'choice_label' => "lastname",
                'disabled' => false,
                'placeholder' => '--'
            ]);

        if ($this->hasDeputy($options)) {
            $builder->add('deputy', EntityType::class, [
                'label' => 'Suppléant',
                'attr' => ['readonly' => true],
                'row_attr' => ["id" => "attendance_deputy_group", "class" => 'd-none'],
                'required' => true,
                'class' => User::class,
                'query_builder' => $this->userRepository->findDeputyById($options['deputyId']),
                'choice_label' => 'deputy.lastName',
                'disabled' => false,
            ]);
        }
    }

    private function getAttendanceValues(Convocation $convocation, ?bool $isRemoteAllowed): array
    {
        $values = [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
            'Remplacé par son suppléant' => Convocation::ABSENT_SEND_DEPUTY,
            'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA
        ];

        if ($isRemoteAllowed && Convocation::CATEGORY_CONVOCATION === $convocation->getCategory()) {
            $values = [
                'Présent' => Convocation::PRESENT,
                'Présent à distance' => Convocation::REMOTE,
                'Absent' => Convocation::ABSENT,
                'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA,
                'Remplacé par son suppléant' => Convocation::ABSENT_SEND_DEPUTY,
            ];
        }

        return $values;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'isRemoteAllowed' => false,
            'convocation' => null,
            'sitting' => null,
            'deputyId' => null,
            'toExclude' => null
        ]);
    }

    private function hasDeputy(array $options): bool
    {
        if (!$options['toExclude'][0]->getDeputy()) {
            return false;
        }
        return true;
    }
}

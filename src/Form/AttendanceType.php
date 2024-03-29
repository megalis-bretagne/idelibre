<?php

namespace App\Form;

use App\Entity\Convocation;
use App\Entity\User;
use App\Repository\UserRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendanceType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
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
            ]);

        if ($options['deputyId'] !== null) {
            $builder->add('deputyId', EntityType::class, [
                'label' => 'Suppléant',
                'attr' => ['readonly' => true, 'class' => "select-readonly" ],
                'row_attr' => ["id" => "attendance_deputy_group", "class" => 'd-none'],
                'required' => true,
                'class' => User::class,
                'query_builder' => $this->userRepository->findDeputyById(['id' => $options['deputyId']]),
                'choice_label' => fn (User $user) => $this->formatName($user),
                'disabled' => false,
            ]);
        }
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

    private function formatName(User $user): string
    {
        return $user->getLastName() . ' ' . $user->getFirstName();
    }


    private function getAttendanceValues(Convocation $convocation, ?bool $isRemoteAllowed): array
    {
        if ($isRemoteAllowed && Convocation::CATEGORY_CONVOCATION === $convocation->getCategory()) {
            if ($convocation->getUser()->getDeputy() !== null) {
                return $this->valuesIfDeputyAndIfRemote();
            }

            if ($convocation->getUser()->getDeputy() === null) {
                return $this->valuesIfNoDeputyAndIfRemote();
            }
        }

        if (!$isRemoteAllowed && Convocation::CATEGORY_CONVOCATION === $convocation->getCategory()) {
            if ($convocation->getUser()->getDeputy() !== null) {
                return $this->valuesIfDeputyAndIfNotRemote();
            }

            if ($convocation->getUser()->getDeputy() === null) {
                return $this->valuesIfNoDeputyAndIfNotRemote();
            }
        }

        return $this->defaultPresenceValues();
    }

    private function valuesIfDeputyAndIfRemote(): array
    {
        return [
            'Présent' => Convocation::PRESENT,
            'Présent à distance' => Convocation::REMOTE,
            'Absent' => Convocation::ABSENT,
            'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA,
            'Remplacé par son suppléant' => Convocation::ABSENT_SEND_DEPUTY,
        ];
    }

    private function valuesIfDeputyAndIfNotRemote(): array
    {
        return [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
            'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA,
            'Remplacé par son suppléant' => Convocation::ABSENT_SEND_DEPUTY,
        ];
    }

    private function valuesIfNoDeputyAndIfRemote(): array
    {
        return [
                'Présent' => Convocation::PRESENT,
                'Présent à distance' => Convocation::REMOTE,
                'Absent' => Convocation::ABSENT,
                'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA,
            ];
    }

    private function valuesIfNoDeputyAndIfNotRemote(): array
    {
        return [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
            'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA,
        ];
    }

    private function defaultPresenceValues(): array
    {
        return [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
            ];
    }
}

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
                'choices' => $this->presenceValues($options['convocation']),
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
            'isMandatorAllowed' => true,
            'convocation' => null,
            'sitting' => null,
            'deputyId' => null,
            'toExclude' => null
        ]);
    }

    private function formatName(User $user): string
    {
        return $user->getLastName() . ' ' . $user->getFirstName();
    }

    private function presenceValues($convocation): array
    {
        $presenceStatusList = [
            'Présent' => Convocation::PRESENT,
            'Absent' => Convocation::ABSENT,
        ];

        if ($convocation->getCategory() === Convocation::CATEGORY_CONVOCATION) {
            if ($convocation->getSitting()->getIsRemoteAllowed() === true) {
                $presenceStatusList = [...$presenceStatusList, ...[ 'Présent à distance' => Convocation::REMOTE ]];
            }

            if ($convocation->getSitting()->isMandatorAllowed() === true) {
                $presenceStatusList = [...$presenceStatusList, ...[ 'Donne pouvoir via procuration' => Convocation::ABSENT_GIVE_POA ]];
            }

            if ($convocation->getUser()->getDeputy() !== null) {
                $presenceStatusList = [...$presenceStatusList, ...[ 'Remplacé par son suppléant' => Convocation::ABSENT_SEND_DEPUTY ]];
            }
        }

        return $presenceStatusList;
    }
}

<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Type;
use App\Entity\User;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\LsChoiceType;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Type|null $type */
        $type = $builder->getData();

        $eluAssocie = 'Elus associés';
        $employeeAssocie = 'Personnels administratifs, Administrateurs, Gestionnaires de séance associés';
        $guestAssocie = 'Invités associés';
        if (!$options['isNew']) {
            $eluAssocie = 'Elus associés (' . $options['actor'][0]['count'] . ')';
            $employeeAssocie = 'Personnels administratifs, Administrateurs, Gestionnaires de séance associés (' . $options['employee'][0]['count'] . ')';
            $guestAssocie = 'Invités associés (' . $options['guest'][0]['count'] . ')';
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('associatedActors', EntityType::class, [
                'placeholder' => 'Sélectionner les élus',
                'required' => false,
                'label' => $eluAssocie,
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findActorsByStructure($options['structure']),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
                'data' => $this->userRepository->getAssociatedActorsWithType($options['data'] ?? null),
            ])
            ->add('associatedEmployees', EntityType::class, [
                'placeholder' => 'Sélectionner les personnels administratifs',
                'required' => false,
                'label' => $employeeAssocie,
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findInvitableEmployeesByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedInvitableEmployeesWithType($options['data'] ?? null),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('associatedGuests', EntityType::class, [
                'placeholder' => 'Sélectionner les Invités',
                'required' => false,
                'label' => $guestAssocie,
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findGuestsByStructure($options['structure']),
                'data' => $this->userRepository->getAssociatedGuestWithType($options['data'] ?? null),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('isComelus', LsChoiceType::class, [
                'label' => 'Envoyer le dossier via comelus',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$type || $type->getIsComelus(),
            ])
            ->add('isSms', LsChoiceType::class, [
                'label' => 'Notifier les élus via sms',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$type || $type->getIsSms(),
            ])
            ->add('isSmsEmployees', LsChoiceType::class, [
                'label' => 'Notifier les personnels administratifs via sms',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$type || $type->getIsSmsEmployees(),
            ])
            ->add('isSmsGuests', LsChoiceType::class, [
                'label' => 'Notifier les invités via sms',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$type || $type->getIsSmsGuests(),
            ])
            ->add('authorizedSecretaries', EntityType::class, [
                'placeholder' => 'Sélectionner les gestionnaires de séance autorisés',
                'required' => false,
                'label' => 'Gestionnaires de séance autorisés à gérer la séance',
                'class' => User::class,
                'query_builder' => $this->userRepository
                    ->findSecretariesByStructure($options['structure']),
                'choice_label' => fn (User $user) => $this->formatUserString($user),
                'multiple' => true,
            ])
            ->add('reminder', ReminderType::class, [
                'label' => false,
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ]);
    }

    private function formatUserString(User $user): string
    {
        $usernameWithoutSuffix = preg_replace('/@.*/', '', $user->getUsername());

        return sprintf('%s %s <%s>', $user->getFirstName(), $user->getLastName(), $usernameWithoutSuffix);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Type::class,
            'isNew' => false,
            'structure' => null,
            'actor' => null,
            'employee' => null,
            'guest' => null,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\Seance\SittingManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SittingType extends AbstractType
{
    private TypeRepository $typeRepository;
    private SittingManager $sittingManager;

    public function __construct(TypeRepository $typeRepository, SittingManager $sittingManager)
    {
        $this->typeRepository = $typeRepository;
        $this->sittingManager = $sittingManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !isset($options['data']);
        $isAlreadySentConvocation = !$isNew && $this->sittingManager->isAlreadySentConvocation($options['data']);
        $isAlreadySentInvitation = !$isNew && $this->sittingManager->isAlreadySentInvitation($options['data']);

        $builder
            ->add('type', EntityType::class, [
                'label' => 'Type de séance',
                'class' => Type::class,
                'query_builder' => $this->typeRepository->findByStructure($options['structure']),
                'choice_label' => 'name',
                'disabled' => !$isNew,
            ])
            ->add('date', null, [
                'label' => 'Date et heure',
                'required' => true,
                'widget' => 'single_text',
                'view_timezone' => $this->getTimeZone($options['structure']),
                'disabled' => !$isNew,
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
            ])
            ->add('convocationFile', LsFileType::class, [
                'label' => $isNew ? 'Fichier de convocation' : 'Remplacer le fichier de convocation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => $isNew,
                'file_name' => $this->getConvocationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentConvocation,
            ])
            ->add('invitationFile', LsFileType::class, [
                'label' => $isNew ? 'Fichier d\'invitation' : 'Remplacer le fichier d\'invitation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => false,
                'file_name' => $this->getInvitationFileName($options['data'] ?? null),
                'disabled' => $isAlreadySentInvitation,
            ])
            ->add('structure', HiddenType::class, [
                'data' => $options['structure'],
                'data_class' => null,
            ])
            ->get('structure')->addModelTransformer(new CallbackTransformer(
                fn () => '',
                fn () => $options['structure']
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sitting::class,
            'structure' => null,
        ]);
    }

    private function getConvocationFileName(?Sitting $sitting): ?string
    {
        if (!$sitting) {
            return null;
        }
        if (!empty($sitting->getConvocationFile())) {
            return $sitting->getConvocationFile()->getName();
        }

        return null;
    }

    private function getInvitationFileName(?Sitting $sitting): ?string
    {
        if (!$sitting) {
            return null;
        }
        if (!empty($sitting->getInvitationFile())) {
            return $sitting->getInvitationFile()->getName();
        }

        return null;
    }

    private function getTimeZone(Structure $structure): string
    {
        return $structure->getTimezone()->getName();
    }
}

<?php

namespace App\Form;

use App\Entity\Sitting;
use App\Entity\Structure;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SittingType extends AbstractType
{
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !isset($options['data']);

        $builder
            ->add('type', EntityType::class, [
                'label' => 'type de séance',
                'class' => Type::class,
                'query_builder' => $this->typeRepository->findByStructure($options['structure']),
                'choice_label' => 'name',
            ])
            ->add('date', null, [
                'label' => 'Date et heure',
                'required' => true,
                'widget' => 'single_text',
                'view_timezone' => $this->getTimeZone($options['structure']),
            ])
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
            ])
            ->add('convocationFile', FileType::class, [
                'label' => $isNew ? 'Fichier de convocation' : 'Remplacer le fichier de convocation',
                'attr' => [
                    'placeholder' => 'Sélectionner un fichier',
                    'accept' => '.pdf,.PDF',
                ],
                'mapped' => false,
                'required' => $isNew,
            ])
            ->add('structure', HiddenType::class, [
                'data' => $options['structure'],
                'data_class' => null
            ])
            ->get("structure")->addModelTransformer(new CallbackTransformer(
                fn() => '',
                fn() => $options['structure']
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sitting::class,
            'structure' => null,
        ]);
    }

    private function getTimeZone(Structure $structure): string
    {
        return $structure->getTimezone()->getName();
    }
}

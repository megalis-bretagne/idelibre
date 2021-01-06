<?php

namespace App\Form;

use App\Entity\EmailTemplate;
use App\Entity\Type;
use App\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isDefaultTemplate = $this->isDefaultTemplate($options['data'] ?? null);

        if (!$isDefaultTemplate) {
            $builder->add('category', HiddenType::class, [
                'data' => EmailTemplate::CATEGORY_CONVOCATION,
            ])
                ->add('name', TextType::class, [
                    'label' => 'Intitulé',
                ])
                ->add('type', EntityType::class, [
                    'label' => 'Type de séance',
                    'placeholder' => 'Sélectionner un type',
                    'required' => false,
                    'class' => Type::class,
                    'query_builder' => $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure(
                        $options['structure'],
                        $options['data'] ?? null
                    ),
                    'choice_label' => 'name',
                ]);
        }

        $builder->add('subject', TextType::class, [
            'label' => 'Objet',
        ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 15],
            ]);

        if (!$this->isForgetPassword($options['data'] ?? null)) {
            $builder->add('isAttachment', CheckboxType::class, [
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
                'label' => 'Joindre le fichier de convocation',
            ]);
        }

        $builder->add('structure', HiddenType::class, [
            'data' => $options['structure'],
            'data_class' => null,
        ]);

        $builder->get('structure')->addModelTransformer(new CallbackTransformer(
            fn () => '',
            fn () => $options['structure']
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
            'structure' => null,
        ]);
    }

    private function isDefaultTemplate(?EmailTemplate $emailTemplate): bool
    {
        return $emailTemplate && $emailTemplate->getIsDefault();
    }

    private function isForgetPassword(?EmailTemplate $emailTemplate): bool
    {
        return $emailTemplate && EmailTemplate::CATEGORY_RESET_PASSWORD === $emailTemplate->getCategory();
    }
}

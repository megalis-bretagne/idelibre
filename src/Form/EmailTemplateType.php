<?php

namespace App\Form;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\LsChoiceType;
use App\Repository\TypeRepository;
use App\Service\Email\EmailData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $emailTemplate = $builder->getData();

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

        $builder->add('format', LsChoiceType::class, [
            'label' => "Format de l'email",
            'choices' => [
                'Html' => EmailData::FORMAT_HTML,
                'Texte' => EmailData::FORMAT_TEXT,
            ],
            'data' => !$emailTemplate ? EmailData::FORMAT_HTML : $emailTemplate->getFormat(),
        ]);

        $builder->add('subject', TextType::class, [
            'label' => 'Objet',
        ])
            ->add('content', TextareaType::class, [
                'sanitize_html' => true,
                'sanitizer' => 'emailtemplate_content',
                'label' => 'Contenu',
                'attr' => ['rows' => 15],
            ]);

        if (!$this->IsEmailRecapitulatif($options['data'] ?? null)) {
            $builder->add('isAttachment', LsChoiceType::class, [
                'required' => false,
                'label' => $this->isConvocation($options['data'] ?? null) ? 'Joindre le fichier de convocation' : 'Joindre le fichier d\'invitation',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => !$emailTemplate ? false : $emailTemplate->getIsAttachment(),
            ]);
        }

        $builder->add('structure', HiddenEntityType::class, [
            'data' => $options['structure'],
            'class_name' => Structure::class,
        ]);
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

    private function isConvocation(?EmailTemplate $emailTemplate): bool
    {
        if (!$emailTemplate) {
            return true;
        }

        return EmailTemplate::CATEGORY_CONVOCATION === $emailTemplate->getCategory();
    }

    private function IsEmailRecapitulatif(?EmailTemplate $emailTemplate): bool
    {
        return $emailTemplate && EmailTemplate::CATEGORY_RECAPITULATIF === $emailTemplate->getCategory();
    }

}

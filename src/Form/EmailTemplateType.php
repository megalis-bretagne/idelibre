<?php

namespace App\Form;

use App\Entity\EmailTemplate;
use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\EmailTemplate\HtmlTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    // TODO REFATOR THIS
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isDefault = $this->isDefaultTemplate($options['data'] ?? null);
        $isNew = $this->isNew($options['data'] ?? null);


        if (!$isDefault) {
            $builder->add('category', ChoiceType::class, [
                'choices' => [
                    'Convocation' => EmailTemplate::CONVOCATION,
                    'Invitation' => EmailTemplate::INVITATION
                ]
            ])
                ->add('name', TextType::class, [
                    'label' => 'Intitulé'
                ])
                ->add('type', EntityType::class, [
                    'label' => 'Type de séance',
                    'placeholder' => 'Sélectionner un type',
                    'required' => false,
                    'class' => Type::class,
                    'query_builder' => $this->typeRepository->findNotAssociatedWithOtherTemplateByStructure($options['structure'], $options['data'] ?? null),
                    'choice_label' => 'name',
                ]);
        }

        $builder->add('subject', TextType::class, [
            'label' => 'Objet'
        ]);

        $contentOptions = [
            'label' => 'Contenu',
            'attr' => [
                'rows' => 15]];

        if ($isNew) {
            $contentOptions['data'] = HtmlTag::START_HTML . HtmlTag::END_HTML;
        }

        $builder->add('content', TextareaType::class, $contentOptions);

        if (!$this->isForgetPassword($options['data'] ?? null)) {
            $builder->add('isAttachment', CheckboxType::class, [
                'required' => false,
                'label' => 'Joindre le fichier de convocation'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
            'structure' => null
        ]);
    }

    private function isDefaultTemplate(?EmailTemplate $emailTemplate)
    {
        return $emailTemplate && $emailTemplate->getIsDefault();
    }

    private function isNew(?EmailTemplate $emailTemplate)
    {
        return !($emailTemplate && $emailTemplate->getId());
    }

    private function isForgetPassword(?EmailTemplate $emailTemplate)
    {
        return $emailTemplate && $emailTemplate->getCategory() === EmailTemplate::RESET_PASSWORD;
    }
}

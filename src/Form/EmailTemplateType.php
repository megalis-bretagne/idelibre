<?php

namespace App\Form;

use App\Entity\EmailTemplate;
use App\Entity\Structure;
use App\Entity\Type;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\LsChoiceType;
use App\Repository\TypeRepository;
use App\Service\Base64_encoder\Encoder;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    public function __construct(
        private readonly TypeRepository $typeRepository,
        private readonly ParameterBagInterface $bag,
        private readonly Encoder $encoder
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var EmailTemplate|null $emailTemplate */
        $emailTemplate = $builder->getData();

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
            ->add('content', TinymceType::class, [
                "attr" => [
                    'skin' => 'oxide',
                    'plugins' => "advlist autolink link image media table lists code paste",
                    'menubar' => false,
                    'toolbar' => 'bold italic underline | bullist numlist | table link image | undo redo | fontfamily fontsize', 'forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent',
                    'valid_elements' => 'strong,em,span[style],a[href]',
                    'inline' => true,
                    'block_unsupported_drop' => false,
                    'images_file_types' => 'jpg,png,jpeg, JPEG',
                    'automatic_uploads' => true,
                    'images_upload_base_path' => $this->bag->get('base_url'),
                    'images_upload_url' => '/api/tinymce-upload/image',
                    'file_picker_types' => 'image',
                    'images_reuse_filename' => true,
                    'paste_data_images' => true,
                ],
                'label' => 'Contenu',
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

    public function configureOptions(OptionsResolver $resolver): void
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

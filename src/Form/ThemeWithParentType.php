<?php

namespace App\Form;

use App\Entity\Structure;
use App\Entity\Theme;
use App\Form\Type\HiddenEntityType;
use App\Repository\ThemeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeWithParentType extends AbstractType
{
    private ThemeRepository $themeRepository;

    public function __construct(ThemeRepository $themeRepository)
    {
        $this->themeRepository = $themeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $theme = $builder->getData();
        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('parentTheme', EntityType::class, [
                'mapped' => false,
                'label' => 'Sous-thème de',
                'required' => false,
                'class' => Theme::class,
                'choice_label' => function (Theme $theme) {
                    $margin = '';
                    for ($i = 1; $i < $theme->getLvl(); ++$i) {
                        $margin .= '--';
                    }

                    return $margin . ' ' . $theme->getName();
                },

                'multiple' => false,
                'query_builder' => $theme ? $this->themeRepository->getNotChildrenNode($theme) :  $this->themeRepository->findChildrenFromStructure($options['structure']),
            ])
            ->add('structure', HiddenEntityType::class, [
                'data' => $options['structure'],
                'class_name' => Structure::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
            'structure' => null,
        ]);
    }
}

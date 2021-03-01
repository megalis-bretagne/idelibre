<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LsFileType extends AbstractType
{
    public function getParent(): ?string
    {
        return FileType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'remove_icon' => 'fa fa-trash-alt',
            'file_name' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, [
            'remove_icon' => $options['remove_icon'],
        ]);
        if (!empty($options['file_name'])) {
            $view->vars['file_name'] = $options['file_name'];
        }
    }
}

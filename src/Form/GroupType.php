<?php

namespace App\Form;

use App\Entity\Group;
use App\Form\Type\LsChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('isStructureCreator', LsChoiceType::class, [
                'label' => 'Autoriser la création de structures',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'data' => $this->isAutorized($options['group']),
            ])
        ;

        if ($options['isNew']) {
            $builder->add('user', SuperUserType::class, [
                'mapped' => false,
                'label' => false,
                'entropyForUser' => $options['entropyForUser'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'isNew' => false,
            'entropyForUser' => null,
            'group' => null,
        ]);
    }

    public function isAutorized(Group $group): ?bool
    {
        return !$group ? false : $group->getIsStructureCreator();
    }
}

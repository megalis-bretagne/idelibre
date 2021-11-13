<?php

namespace App\Form;

use App\Entity\ApiRole;
use App\Entity\ApiUser;
use App\Entity\Structure;
use App\Form\Type\HiddenEntityType;
use App\Service\role\RoleManager;
use App\Util\TokenUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiUserType extends AbstractType
{
    public function __construct(private RoleManager $roleManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = !isset($options['data']);

        $builder
            ->add('name', TextType::class, [
                'label' => 'Intitulé',
            ])
            ->add('token', TextType::class, [
                'label' => "Clé d'api",
                'data' => $this->getTokenValue($options),
            ]);

        $builder->add('apiRole', HiddenEntityType::class, [
            'data' => $this->roleManager->getApiStructureAdminRole(),
            'class_name' => ApiRole::class,
        ]);

        $builder->add('structure', HiddenEntityType::class, [
            'data' => $options['structure'],
            'class_name' => Structure::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApiUser::class,
            'structure' => null,
        ]);
    }

    private function getTokenValue(array $options)
    {
        //$isNew ? TokenUtil::genToken() : $options['data']->getToken()
        if (!isset($options['data'])) {
            return TokenUtil::genToken();
        }

        /** @var ApiUser $apiUser */
        $apiUser = $options['data'];

        return $apiUser->getToken();
    }
}

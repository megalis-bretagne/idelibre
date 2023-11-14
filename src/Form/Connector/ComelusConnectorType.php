<?php

namespace App\Form\Connector;

use App\Entity\Connector\ComelusConnector;
use App\Form\Type\LsChoiceType;
use App\Service\Connector\ComelusConnectorManager;
use Libriciel\ComelusApiWrapper\ComelusException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ComelusConnectorType extends AbstractType
{
    private ComelusConnectorManager $comelusConnectorManager;

    public function __construct(ComelusConnectorManager $comelusConnectorManager)
    {
        $this->comelusConnectorManager = $comelusConnectorManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ComelusConnector $comelusConnector */
        $comelusConnector = $options['data'] ?? null;

        $builder
            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'Url',
                'constraints' => [new Length(['max' => ComelusConnector::MAX_URL_LENGTH])],
            ])
            ->add('apiKey', TextType::class, [
                'required' => true,
                'label' => 'Clé d\'api',
                'constraints' => [new Length(['max' => ComelusConnector::MAX_API_KEY_LENGTH])],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Texte d\'accompagnement',
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('active', LsChoiceType::class, [
                'label' => 'Actif',
                'choices' => [
                    "Oui" => true,
                    "Non" => false,
                ]
            ])
            ->add('mailingListId', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'choississez une liste',
                'choices' => $this->getAvailableOptions($comelusConnector->getUrl(), $comelusConnector->getApiKey()), // do only in get not post !
                'label' => 'Liste de diffusion',
            ]);

        $builder->get('mailingListId')->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ComelusConnector::class,
        ]);
    }

    private function getAvailableOptions(?string $url, ?string $apiKey): ?array
    {
        if (null === $apiKey || null == $url) {
            return null;
        }

        try {
            $mailingLists = $this->comelusConnectorManager->getMailingLists($url, $apiKey);
        } catch (ComelusException $e) {
            return null;
        }

        $formattedList = [];
        foreach ($mailingLists as $mailingList) {
            $formattedList[$mailingList['name']] = $mailingList['id'];
        }

        return $formattedList;
    }
}

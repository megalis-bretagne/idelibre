<?php

namespace App\Form;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the custom form field type used to add a hidden entity
 *
 * See https://symfony.com/doc/current/form/create_custom_field_type.html
 */
class HiddenEntityType extends HiddenType implements DataTransformerInterface
{
    private ?string $className;



    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'class_name' => null
        ]);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $this->className = $options['class_name'];
        $builder->addModelTransformer($this);
    }


    public function transform(mixed $value): string
    {
        dump($value);
        //return 'OK';
     /*   if (!$value instanceof $this->className) {
            throw new TransformationFailedException('Value must be an instance of ' . $this->className);
        }
*/
        return $value->getId();
    }

    public function reverseTransform(mixed $value)
    {
        dd($value);
        try {
            $repository = $this->managerRegistry->getRepository($this->className);
            $entity = $repository->findOneBy(["id" => $value]);
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage());
        }

        if ($entity === null) {
            throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->className, $value));
        }

        return $entity;
    }
}

<?php


declare(strict_types=1);

use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );
  /*  $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_ORM_29);
*/

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class)->call('configure', [[\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        new \Rector\Php80\ValueObject\AnnotationToAttribute('required', 'Symfony\\Contracts\\Service\\Attribute\\Required'),
        new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route'),
        new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted', 'Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted'),
    ])]]);


    //$services->set(ClassPropertyAssignToConstructorPromotionRector::class);

    //ClassPropertyAssignToConstructorPromotionRector

/*
    $services = $containerConfigurator->services();
    $services->set(AnnotationToAttributeRector::class)
        ->call('configure', [[
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
                new AnnotationToAttribute('Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity', 'Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity'),
            ]),
        ]]);
*/

    /*  $services = $containerConfigurator->services();
      $services->set(AnnotationToAttributeRector::class)
          ->call('configure', [[
              AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
  //                new AnnotationToAttribute('Symfony\Component\Serializer\Annotation\Groups', 'Symfony\Component\Serializer\Annotation\Groups'),
  //                new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted', 'Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted'),
              ]),
          ]]);
    */
};



/*



<?php

declare(strict_types=1);

use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

// https://github.com/rectorphp/rector-symfony
return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_ORM_29);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(AnnotationToAttributeRector::class)
        ->call('configure', [[
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
                new AnnotationToAttribute('Symfony\Component\Serializer\Annotation\Groups', 'Symfony\Component\Serializer\Annotation\Groups'),
                new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted', 'Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted'),
            ]),
        ]]);
};






$services->set(AnnotationToAttributeRector::class)
          ->call('configure', [[
              AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
  //                new AnnotationToAttribute('Symfony\Component\Serializer\Annotation\Groups', 'Symfony\Component\Serializer\Annotation\Groups'),
  //                new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted', 'Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted'),
              ]),
          ]]);


 */

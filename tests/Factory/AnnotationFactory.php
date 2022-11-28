<?php

namespace App\Tests\Factory;

use App\Entity\Annotation;
use App\Repository\AnnotationRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Annotation>
 *
 * @method static           Annotation|Proxy createOne(array $attributes = [])
 * @method static           Annotation[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static           Annotation[]|Proxy[] createSequence(array|callable $sequence)
 * @method static           Annotation|Proxy find(object|array|mixed $criteria)
 * @method static           Annotation|Proxy findOrCreate(array $attributes)
 * @method static           Annotation|Proxy first(string $sortedField = 'id')
 * @method static           Annotation|Proxy last(string $sortedField = 'id')
 * @method static           Annotation|Proxy random(array $attributes = [])
 * @method static           Annotation|Proxy randomOrCreate(array $attributes = [])
 * @method static           Annotation[]|Proxy[] all()
 * @method static           Annotation[]|Proxy[] findBy(array $attributes)
 * @method static           Annotation[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static           Annotation[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static           AnnotationRepository|RepositoryProxy repository()
 * @method Annotation|Proxy create(array|callable $attributes = [])
 */
final class AnnotationFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'rect' => [],
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Annotation $annotation): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Annotation::class;
    }
}

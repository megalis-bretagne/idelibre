<?php

namespace App\Tests\Factory;

use App\Entity\AnnotationUser;
use App\Repository\AnnotationUserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AnnotationUser>
 *
 * @method static               AnnotationUser|Proxy createOne(array $attributes = [])
 * @method static               AnnotationUser[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static               AnnotationUser[]|Proxy[] createSequence(array|callable $sequence)
 * @method static               AnnotationUser|Proxy find(object|array|mixed $criteria)
 * @method static               AnnotationUser|Proxy findOrCreate(array $attributes)
 * @method static               AnnotationUser|Proxy first(string $sortedField = 'id')
 * @method static               AnnotationUser|Proxy last(string $sortedField = 'id')
 * @method static               AnnotationUser|Proxy random(array $attributes = [])
 * @method static               AnnotationUser|Proxy randomOrCreate(array $attributes = [])
 * @method static               AnnotationUser[]|Proxy[] all()
 * @method static               AnnotationUser[]|Proxy[] findBy(array $attributes)
 * @method static               AnnotationUser[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static               AnnotationUser[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static               AnnotationUserRepository|RepositoryProxy repository()
 * @method AnnotationUser|Proxy create(array|callable $attributes = [])
 */
final class AnnotationUserFactory extends ModelFactory
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
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(AnnotationUser $annotationUser): void {})
        ;
    }

    protected static function getClass(): string
    {
        return AnnotationUser::class;
    }
}

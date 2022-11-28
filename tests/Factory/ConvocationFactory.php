<?php

namespace App\Tests\Factory;

use App\Entity\Convocation;
use App\Repository\ConvocationRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Convocation>
 *
 * @method static            Convocation|Proxy createOne(array $attributes = [])
 * @method static            Convocation[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static            Convocation[]|Proxy[] createSequence(array|callable $sequence)
 * @method static            Convocation|Proxy find(object|array|mixed $criteria)
 * @method static            Convocation|Proxy findOrCreate(array $attributes)
 * @method static            Convocation|Proxy first(string $sortedField = 'id')
 * @method static            Convocation|Proxy last(string $sortedField = 'id')
 * @method static            Convocation|Proxy random(array $attributes = [])
 * @method static            Convocation|Proxy randomOrCreate(array $attributes = [])
 * @method static            Convocation[]|Proxy[] all()
 * @method static            Convocation[]|Proxy[] findBy(array $attributes)
 * @method static            Convocation[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static            Convocation[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static            ConvocationRepository|RepositoryProxy repository()
 * @method Convocation|Proxy create(array|callable $attributes = [])
 */
final class ConvocationFactory extends ModelFactory
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
            'isRead' => true,
            'isActive' => true,
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Convocation $convocation): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Convocation::class;
    }
}

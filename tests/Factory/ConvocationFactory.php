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
 * @method Convocation|Proxy create(array|callable $attributes = [])
 * @method static            Convocation|Proxy createOne(array $attributes = [])
 * @method static            Convocation|Proxy find(object|array|mixed $criteria)
 * @method static            Convocation|Proxy findOrCreate(array $attributes)
 * @method static            Convocation|Proxy first(string $sortedField = 'id')
 * @method static            Convocation|Proxy last(string $sortedField = 'id')
 * @method static            Convocation|Proxy random(array $attributes = [])
 * @method static            Convocation|Proxy randomOrCreate(array $attributes = [])
 * @method static            ConvocationRepository|RepositoryProxy repository()
 * @method static            Convocation[]|Proxy[] all()
 * @method static            Convocation[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static            Convocation[]|Proxy[] createSequence(array|callable $sequence)
 * @method static            Convocation[]|Proxy[] findBy(array $attributes)
 * @method static            Convocation[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static            Convocation[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class ConvocationFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'category' => self::faker()->text(255),
            'isActive' => true,
            'isRead' => false,
            'sitting' => SittingFactory::new(),
            'user' => UserFactory::new(),
            'deputy' => null
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Convocation $convocation): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Convocation::class;
    }
}

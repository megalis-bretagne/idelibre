<?php

namespace App\Tests\Factory;

use App\Entity\Timezone;
use App\Repository\TimezoneRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Timezone>
 *
 * @method static Timezone|Proxy createOne(array $attributes = [])
 * @method static Timezone[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Timezone[]|Proxy[] createSequence(array|callable $sequence)
 * @method static Timezone|Proxy find(object|array|mixed $criteria)
 * @method static Timezone|Proxy findOrCreate(array $attributes)
 * @method static Timezone|Proxy first(string $sortedField = 'id')
 * @method static Timezone|Proxy last(string $sortedField = 'id')
 * @method static Timezone|Proxy random(array $attributes = [])
 * @method static Timezone|Proxy randomOrCreate(array $attributes = [])
 * @method static Timezone[]|Proxy[] all()
 * @method static Timezone[]|Proxy[] findBy(array $attributes)
 * @method static Timezone[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Timezone[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static TimezoneRepository|RepositoryProxy repository()
 * @method Timezone|Proxy create(array|callable $attributes = [])
 */
final class TimezoneFactory extends ModelFactory
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
            'name' => self::faker()->text(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Timezone $timezone): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Timezone::class;
    }
}

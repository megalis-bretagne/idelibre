<?php

namespace App\Tests\Factory;

use App\Entity\Timezone;
use App\Repository\TimezoneRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Timezone>
 *
 * @method Timezone|Proxy create(array|callable $attributes = [])
 * @method static         Timezone|Proxy createOne(array $attributes = [])
 * @method static         Timezone|Proxy find(object|array|mixed $criteria)
 * @method static         Timezone|Proxy findOrCreate(array $attributes)
 * @method static         Timezone|Proxy first(string $sortedField = 'id')
 * @method static         Timezone|Proxy last(string $sortedField = 'id')
 * @method static         Timezone|Proxy random(array $attributes = [])
 * @method static         Timezone|Proxy randomOrCreate(array $attributes = [])
 * @method static         TimezoneRepository|RepositoryProxy repository()
 * @method static         Timezone[]|Proxy[] all()
 * @method static         Timezone[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static         Timezone[]|Proxy[] createSequence(array|callable $sequence)
 * @method static         Timezone[]|Proxy[] findBy(array $attributes)
 * @method static         Timezone[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static         Timezone[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class TimezoneFactory extends ModelFactory
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
            'name' => self::faker()->text(),
            'info' => self::faker()->timezone,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Timezone $timezone): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Timezone::class;
    }
}

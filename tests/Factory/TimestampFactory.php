<?php

namespace App\Tests\Factory;

use App\Entity\Timestamp;
use App\Repository\TimestampRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Timestamp>
 *
 * @method static          Timestamp|Proxy createOne(array $attributes = [])
 * @method static          Timestamp[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static          Timestamp[]|Proxy[] createSequence(array|callable $sequence)
 * @method static          Timestamp|Proxy find(object|array|mixed $criteria)
 * @method static          Timestamp|Proxy findOrCreate(array $attributes)
 * @method static          Timestamp|Proxy first(string $sortedField = 'id')
 * @method static          Timestamp|Proxy last(string $sortedField = 'id')
 * @method static          Timestamp|Proxy random(array $attributes = [])
 * @method static          Timestamp|Proxy randomOrCreate(array $attributes = [])
 * @method static          Timestamp[]|Proxy[] all()
 * @method static          Timestamp[]|Proxy[] findBy(array $attributes)
 * @method static          Timestamp[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static          Timestamp[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          TimestampRepository|RepositoryProxy repository()
 * @method Timestamp|Proxy create(array|callable $attributes = [])
 */
final class TimestampFactory extends ModelFactory
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
            'filePathContent' => self::faker()->text(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Timestamp $timestamp): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Timestamp::class;
    }
}

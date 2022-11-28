<?php

namespace App\Tests\Factory;

use App\Entity\Structure;
use App\Repository\StructureRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Structure>
 *
 * @method static          Structure|Proxy createOne(array $attributes = [])
 * @method static          Structure[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static          Structure[]|Proxy[] createSequence(array|callable $sequence)
 * @method static          Structure|Proxy find(object|array|mixed $criteria)
 * @method static          Structure|Proxy findOrCreate(array $attributes)
 * @method static          Structure|Proxy first(string $sortedField = 'id')
 * @method static          Structure|Proxy last(string $sortedField = 'id')
 * @method static          Structure|Proxy random(array $attributes = [])
 * @method static          Structure|Proxy randomOrCreate(array $attributes = [])
 * @method static          Structure[]|Proxy[] all()
 * @method static          Structure[]|Proxy[] findBy(array $attributes)
 * @method static          Structure[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static          Structure[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          StructureRepository|RepositoryProxy repository()
 * @method Structure|Proxy create(array|callable $attributes = [])
 */
final class StructureFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->text(),
            'replyTo' => self::faker()->text(),
            'suffix' => self::faker()->text(),
            'legacyConnectionName' => self::faker()->text(),
            'isActive' => true,
            'minimumEntropy' => self::faker()->randomNumber(),
            'timezone' => TimezoneFactory::new(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Structure $structure): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Structure::class;
    }
}

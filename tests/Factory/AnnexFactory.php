<?php

namespace App\Tests\Factory;

use App\Entity\Annex;
use App\Repository\AnnexRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Annex>
 *
 * @method static      Annex|Proxy createOne(array $attributes = [])
 * @method static      Annex[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static      Annex[]|Proxy[] createSequence(array|callable $sequence)
 * @method static      Annex|Proxy find(object|array|mixed $criteria)
 * @method static      Annex|Proxy findOrCreate(array $attributes)
 * @method static      Annex|Proxy first(string $sortedField = 'id')
 * @method static      Annex|Proxy last(string $sortedField = 'id')
 * @method static      Annex|Proxy random(array $attributes = [])
 * @method static      Annex|Proxy randomOrCreate(array $attributes = [])
 * @method static      Annex[]|Proxy[] all()
 * @method static      Annex[]|Proxy[] findBy(array $attributes)
 * @method static      Annex[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static      Annex[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static      AnnexRepository|RepositoryProxy repository()
 * @method Annex|Proxy create(array|callable $attributes = [])
 */
final class AnnexFactory extends ModelFactory
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
            'rank' => self::faker()->randomNumber(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Annex $annex): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Annex::class;
    }
}

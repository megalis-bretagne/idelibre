<?php

namespace App\Tests\Factory;

use App\Entity\Gdpr\DataControllerGdpr;
use App\Repository\DataControllerGdprRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<DataControllerGdpr>
 *
 * @method static                   DataControllerGdpr|Proxy createOne(array $attributes = [])
 * @method static                   DataControllerGdpr[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static                   DataControllerGdpr[]|Proxy[] createSequence(array|callable $sequence)
 * @method static                   DataControllerGdpr|Proxy find(object|array|mixed $criteria)
 * @method static                   DataControllerGdpr|Proxy findOrCreate(array $attributes)
 * @method static                   DataControllerGdpr|Proxy first(string $sortedField = 'id')
 * @method static                   DataControllerGdpr|Proxy last(string $sortedField = 'id')
 * @method static                   DataControllerGdpr|Proxy random(array $attributes = [])
 * @method static                   DataControllerGdpr|Proxy randomOrCreate(array $attributes = [])
 * @method static                   DataControllerGdpr[]|Proxy[] all()
 * @method static                   DataControllerGdpr[]|Proxy[] findBy(array $attributes)
 * @method static                   DataControllerGdpr[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static                   DataControllerGdpr[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static                   DataControllerGdprRepository|RepositoryProxy repository()
 * @method DataControllerGdpr|Proxy create(array|callable $attributes = [])
 */
final class DataControllerGdprFactory extends ModelFactory
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
            'address' => self::faker()->text(),
            'siret' => self::faker()->text(),
            'ape' => self::faker()->text(),
            'phone' => self::faker()->text(),
            'email' => self::faker()->text(),
            'representative' => self::faker()->text(),
            'quality' => self::faker()->text(),
            'dpoName' => self::faker()->text(),
            'dpoEmail' => self::faker()->text(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(DataControllerGdpr $dataControllerGdpr): void {})
        ;
    }

    protected static function getClass(): string
    {
        return DataControllerGdpr::class;
    }
}

<?php

namespace App\Tests\Factory;

use App\Entity\Gdpr\GdprHosting;
use App\Repository\GdprHostingRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<GdprHosting>
 *
 * @method static            GdprHosting|Proxy createOne(array $attributes = [])
 * @method static            GdprHosting[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static            GdprHosting[]|Proxy[] createSequence(array|callable $sequence)
 * @method static            GdprHosting|Proxy find(object|array|mixed $criteria)
 * @method static            GdprHosting|Proxy findOrCreate(array $attributes)
 * @method static            GdprHosting|Proxy first(string $sortedField = 'id')
 * @method static            GdprHosting|Proxy last(string $sortedField = 'id')
 * @method static            GdprHosting|Proxy random(array $attributes = [])
 * @method static            GdprHosting|Proxy randomOrCreate(array $attributes = [])
 * @method static            GdprHosting[]|Proxy[] all()
 * @method static            GdprHosting[]|Proxy[] findBy(array $attributes)
 * @method static            GdprHosting[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static            GdprHosting[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static            GdprHostingRepository|RepositoryProxy repository()
 * @method GdprHosting|Proxy create(array|callable $attributes = [])
 */
final class GdprHostingFactory extends ModelFactory
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
            'companyName' => self::faker()->text(),
            'address' => self::faker()->text(),
            'representative' => self::faker()->text(),
            'quality' => self::faker()->text(),
            'siret' => self::faker()->text(),
            'ape' => self::faker()->text(),
            'companyPhone' => self::faker()->text(),
            'companyEmail' => self::faker()->text(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(GdprHosting $gdprHosting): void {})
        ;
    }

    protected static function getClass(): string
    {
        return GdprHosting::class;
    }
}

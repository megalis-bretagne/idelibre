<?php

namespace App\Tests\Factory;

use App\Entity\Otherdoc;
use App\Repository\OtherdocRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Otherdoc>
 *
 * @method Otherdoc|Proxy create(array|callable $attributes = [])
 * @method static         Otherdoc|Proxy createOne(array $attributes = [])
 * @method static         Otherdoc|Proxy find(object|array|mixed $criteria)
 * @method static         Otherdoc|Proxy findOrCreate(array $attributes)
 * @method static         Otherdoc|Proxy first(string $sortedField = 'id')
 * @method static         Otherdoc|Proxy last(string $sortedField = 'id')
 * @method static         Otherdoc|Proxy random(array $attributes = [])
 * @method static         Otherdoc|Proxy randomOrCreate(array $attributes = [])
 * @method static         OtherdocRepository|RepositoryProxy repository()
 * @method static         Otherdoc[]|Proxy[] all()
 * @method static         Otherdoc[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static         Otherdoc[]|Proxy[] createSequence(array|callable $sequence)
 * @method static         Otherdoc[]|Proxy[] findBy(array $attributes)
 * @method static         Otherdoc[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static         Otherdoc[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class OtherdocFactory extends ModelFactory
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
            'name' => self::faker()->text(512),
            'rank' => 1,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Otherdoc $otherdoc): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Otherdoc::class;
    }
}

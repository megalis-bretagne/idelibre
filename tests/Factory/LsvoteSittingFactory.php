<?php

namespace App\Tests\Factory;

use App\Entity\LsvoteSitting;
use App\Entity\Sitting;
use App\Repository\LsvoteSittingRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<LsvoteSitting>
 *
 * @method        LsvoteSitting|Proxy create(array|callable $attributes = [])
 * @method static LsvoteSitting|Proxy createOne(array $attributes = [])
 * @method static LsvoteSitting|Proxy find(object|array|mixed $criteria)
 * @method static LsvoteSitting|Proxy findOrCreate(array $attributes)
 * @method static LsvoteSitting|Proxy first(string $sortedField = 'id')
 * @method static LsvoteSitting|Proxy last(string $sortedField = 'id')
 * @method static LsvoteSitting|Proxy random(array $attributes = [])
 * @method static LsvoteSitting|Proxy randomOrCreate(array $attributes = [])
 * @method static LsvoteSittingRepository|RepositoryProxy repository()
 * @method static LsvoteSitting[]|Proxy[] all()
 * @method static LsvoteSitting[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static LsvoteSitting[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static LsvoteSitting[]|Proxy[] findBy(array $attributes)
 * @method static LsvoteSitting[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static LsvoteSitting[]|Proxy[] randomSet(int $number, array $attributes = [])
 *
 */
final class LsvoteSittingFactory extends ModelFactory
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
            'LsvoteSittingId' => "bac262a3-7478-4777-a399-0f93813c9ebe",
            'results' => [],
            'sitting' => SittingFactory::new()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(LsvoteSitting $lsvoteSitting): void {})
        ;
    }

    protected static function getClass(): string
    {
        return LsvoteSitting::class;
    }
}

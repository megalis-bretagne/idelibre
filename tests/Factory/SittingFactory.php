<?php

namespace App\Tests\Factory;

use App\Entity\Sitting;
use App\Repository\SittingRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Sitting>
 *
 * @method Sitting|Proxy create(array|callable $attributes = [])
 * @method static        Sitting|Proxy createOne(array $attributes = [])
 * @method static        Sitting|Proxy find(object|array|mixed $criteria)
 * @method static        Sitting|Proxy findOrCreate(array $attributes)
 * @method static        Sitting|Proxy first(string $sortedField = 'id')
 * @method static        Sitting|Proxy last(string $sortedField = 'id')
 * @method static        Sitting|Proxy random(array $attributes = [])
 * @method static        Sitting|Proxy randomOrCreate(array $attributes = [])
 * @method static        SittingRepository|RepositoryProxy repository()
 * @method static        Sitting[]|Proxy[] all()
 * @method static        Sitting[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static        Sitting[]|Proxy[] createSequence(array|callable $sequence)
 * @method static        Sitting[]|Proxy[] findBy(array $attributes)
 * @method static        Sitting[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        Sitting[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class SittingFactory extends ModelFactory
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
            'date' => null, // TODO add DATETIME ORM type manually
            'revision' => self::faker()->randomNumber(),
            'isArchived' => false,
            'isRemoteAllowed' => true,
            'convocationFile' => FileFactory::new(
                ['path' => '/tmp/convocation.pdf']
            )
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Sitting $sitting): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Sitting::class;
    }
}

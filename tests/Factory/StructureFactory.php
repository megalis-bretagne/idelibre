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
 * @method Structure|Proxy create(array|callable $attributes = [])
 * @method static          Structure|Proxy createOne(array $attributes = [])
 * @method static          Structure|Proxy find(object|array|mixed $criteria)
 * @method static          Structure|Proxy findOrCreate(array $attributes)
 * @method static          Structure|Proxy first(string $sortedField = 'id')
 * @method static          Structure|Proxy last(string $sortedField = 'id')
 * @method static          Structure|Proxy random(array $attributes = [])
 * @method static          Structure|Proxy randomOrCreate(array $attributes = [])
 * @method static          StructureRepository|RepositoryProxy repository()
 * @method static          Structure[]|Proxy[] all()
 * @method static          Structure[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static          Structure[]|Proxy[] createSequence(array|callable $sequence)
 * @method static          Structure[]|Proxy[] findBy(array $attributes)
 * @method static          Structure[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          Structure[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class StructureFactory extends ModelFactory
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
            'isActive' => true,
            'legacyConnectionName' => self::faker()->text(255),
            'name' => self::faker()->text(255),
            'replyTo' => 'reply@email.fr',
            'suffix' => self::faker()->text(255),
            'timezone' => TimezoneFactory::new(),
            'canEditReplyTo' => true,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Structure $structure): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Structure::class;
    }
}

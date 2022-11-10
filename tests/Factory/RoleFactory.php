<?php

namespace App\Tests\Factory;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Role>
 *
 * @method static Role|Proxy createOne(array $attributes = [])
 * @method static Role[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Role[]|Proxy[] createSequence(array|callable $sequence)
 * @method static Role|Proxy find(object|array|mixed $criteria)
 * @method static Role|Proxy findOrCreate(array $attributes)
 * @method static Role|Proxy first(string $sortedField = 'id')
 * @method static Role|Proxy last(string $sortedField = 'id')
 * @method static Role|Proxy random(array $attributes = [])
 * @method static Role|Proxy randomOrCreate(array $attributes = [])
 * @method static Role[]|Proxy[] all()
 * @method static Role[]|Proxy[] findBy(array $attributes)
 * @method static Role[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Role[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static RoleRepository|RepositoryProxy repository()
 * @method Role|Proxy create(array|callable $attributes = [])
 */
final class RoleFactory extends ModelFactory
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
            'composites' => [],
            'prettyName' => self::faker()->text(),
            'isInStructureRole' => self::faker()->boolean(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Role $role): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Role::class;
    }
}

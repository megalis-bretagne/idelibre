<?php

namespace App\Tests\Factory;

use App\Entity\ApiRole;
use App\Repository\ApiRoleRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ApiRole>
 *
 * @method static        ApiRole|Proxy createOne(array $attributes = [])
 * @method static        ApiRole[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static        ApiRole[]|Proxy[] createSequence(array|callable $sequence)
 * @method static        ApiRole|Proxy find(object|array|mixed $criteria)
 * @method static        ApiRole|Proxy findOrCreate(array $attributes)
 * @method static        ApiRole|Proxy first(string $sortedField = 'id')
 * @method static        ApiRole|Proxy last(string $sortedField = 'id')
 * @method static        ApiRole|Proxy random(array $attributes = [])
 * @method static        ApiRole|Proxy randomOrCreate(array $attributes = [])
 * @method static        ApiRole[]|Proxy[] all()
 * @method static        ApiRole[]|Proxy[] findBy(array $attributes)
 * @method static        ApiRole[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        ApiRole[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        ApiRoleRepository|RepositoryProxy repository()
 * @method ApiRole|Proxy create(array|callable $attributes = [])
 */
final class ApiRoleFactory extends ModelFactory
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
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(ApiRole $apiRole): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ApiRole::class;
    }
}

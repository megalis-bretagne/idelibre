<?php

namespace App\Tests\Factory;

use App\Entity\ApiUser;
use App\Repository\ApiUserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ApiUser>
 *
 * @method static        ApiUser|Proxy createOne(array $attributes = [])
 * @method static        ApiUser[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static        ApiUser[]|Proxy[] createSequence(array|callable $sequence)
 * @method static        ApiUser|Proxy find(object|array|mixed $criteria)
 * @method static        ApiUser|Proxy findOrCreate(array $attributes)
 * @method static        ApiUser|Proxy first(string $sortedField = 'id')
 * @method static        ApiUser|Proxy last(string $sortedField = 'id')
 * @method static        ApiUser|Proxy random(array $attributes = [])
 * @method static        ApiUser|Proxy randomOrCreate(array $attributes = [])
 * @method static        ApiUser[]|Proxy[] all()
 * @method static        ApiUser[]|Proxy[] findBy(array $attributes)
 * @method static        ApiUser[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        ApiUser[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        ApiUserRepository|RepositoryProxy repository()
 * @method ApiUser|Proxy create(array|callable $attributes = [])
 */
final class ApiUserFactory extends ModelFactory
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
            'token' => self::faker()->text(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(ApiUser $apiUser): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ApiUser::class;
    }
}

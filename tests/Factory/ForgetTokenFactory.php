<?php

namespace App\Tests\Factory;

use App\Entity\ForgetToken;
use App\Repository\ForgetTokenRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ForgetToken>
 *
 * @method static            ForgetToken|Proxy createOne(array $attributes = [])
 * @method static            ForgetToken[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static            ForgetToken[]|Proxy[] createSequence(array|callable $sequence)
 * @method static            ForgetToken|Proxy find(object|array|mixed $criteria)
 * @method static            ForgetToken|Proxy findOrCreate(array $attributes)
 * @method static            ForgetToken|Proxy first(string $sortedField = 'id')
 * @method static            ForgetToken|Proxy last(string $sortedField = 'id')
 * @method static            ForgetToken|Proxy random(array $attributes = [])
 * @method static            ForgetToken|Proxy randomOrCreate(array $attributes = [])
 * @method static            ForgetToken[]|Proxy[] all()
 * @method static            ForgetToken[]|Proxy[] findBy(array $attributes)
 * @method static            ForgetToken[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static            ForgetToken[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static            ForgetTokenRepository|RepositoryProxy repository()
 * @method ForgetToken|Proxy create(array|callable $attributes = [])
 */
final class ForgetTokenFactory extends ModelFactory
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
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(ForgetToken $forgetToken): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ForgetToken::class;
    }
}

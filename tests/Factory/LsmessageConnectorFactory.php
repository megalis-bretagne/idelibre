<?php

namespace App\Tests\Factory;

use App\Entity\Connector\LsmessageConnector;
use App\Repository\Connector\LsmessageConnectorRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<LsmessageConnector>
 *
 * @method static                   LsmessageConnector|Proxy createOne(array $attributes = [])
 * @method static                   LsmessageConnector[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static                   LsmessageConnector[]|Proxy[] createSequence(array|callable $sequence)
 * @method static                   LsmessageConnector|Proxy find(object|array|mixed $criteria)
 * @method static                   LsmessageConnector|Proxy findOrCreate(array $attributes)
 * @method static                   LsmessageConnector|Proxy first(string $sortedField = 'id')
 * @method static                   LsmessageConnector|Proxy last(string $sortedField = 'id')
 * @method static                   LsmessageConnector|Proxy random(array $attributes = [])
 * @method static                   LsmessageConnector|Proxy randomOrCreate(array $attributes = [])
 * @method static                   LsmessageConnector[]|Proxy[] all()
 * @method static                   LsmessageConnector[]|Proxy[] findBy(array $attributes)
 * @method static                   LsmessageConnector[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static                   LsmessageConnector[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static                   LsmessageConnectorRepository|RepositoryProxy repository()
 * @method LsmessageConnector|Proxy create(array|callable $attributes = [])
 */
final class LsmessageConnectorFactory extends ModelFactory
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
            // ->afterInstantiate(function(LsmessageConnector $lsmessageConnector): void {})
        ;
    }

    protected static function getClass(): string
    {
        return LsmessageConnector::class;
    }
}

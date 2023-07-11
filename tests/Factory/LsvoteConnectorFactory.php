<?php

namespace App\Tests\Factory;

use App\Entity\Connector\LsvoteConnector;
use App\Repository\LsvoteConnectorRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<LsvoteConnector>
 *
 * @method        LsvoteConnector|Proxy create(array|callable $attributes = [])
 * @method static LsvoteConnector|Proxy createOne(array $attributes = [])
 * @method static LsvoteConnector|Proxy find(object|array|mixed $criteria)
 * @method static LsvoteConnector|Proxy findOrCreate(array $attributes)
 * @method static LsvoteConnector|Proxy first(string $sortedField = 'id')
 * @method static LsvoteConnector|Proxy last(string $sortedField = 'id')
 * @method static LsvoteConnector|Proxy random(array $attributes = [])
 * @method static LsvoteConnector|Proxy randomOrCreate(array $attributes = [])
 * @method static LsvoteConnectorRepository|RepositoryProxy repository()
 * @method static LsvoteConnector[]|Proxy[] all()
 * @method static LsvoteConnector[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static LsvoteConnector[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static LsvoteConnector[]|Proxy[] findBy(array $attributes)
 * @method static LsvoteConnector[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static LsvoteConnector[]|Proxy[] randomSet(int $number, array $attributes = [])

 */
final class LsvoteConnectorFactory extends ModelFactory
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
            'url' => "https://test.fr",
            'apiKey' => "1234",
            'structure' => StructureFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(LsvoteConnector $lsvoteConnector): void {})
        ;
    }

    protected static function getClass(): string
    {
        return LsvoteConnector::class;
    }
}

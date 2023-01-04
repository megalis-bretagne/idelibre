<?php

namespace App\Tests\Factory;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Subscription>
 *
 * @method static          Subscription|Proxy createOne(array $attributes = [])
 * @method static          Subscription[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static          Subscription[]|Proxy[] createSequence(array|callable $sequence)
 * @method static          Subscription|Proxy find(object|array|mixed $criteria)
 * @method static          Subscription|Proxy findOrCreate(array $attributes)
 * @method static          Subscription|Proxy first(string $sortedField = 'id')
 * @method static          Subscription|Proxy last(string $sortedField = 'id')
 * @method static          Subscription|Proxy random(array $attributes = [])
 * @method static          Subscription|Proxy randomOrCreate(array $attributes = [])
 * @method static          Subscription[]|Proxy[] all()
 * @method static          Subscription[]|Proxy[] findBy(array $attributes)
 * @method static          Subscription[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static          Subscription[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static          SubscriptionRepository|RepositoryProxy repository()
 * @method Subscription|Proxy create(array|callable $attributes = [])
 */
final class SubscriptionFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            'accept_mail_recap' => true,
            'createdAt' => new \DateTimeImmutable(),
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Subscription $subscription): void {})
            ;
    }

    protected static function getClass(): string
    {
        return Subscription::class;
    }
}

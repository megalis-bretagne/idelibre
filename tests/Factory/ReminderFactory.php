<?php

namespace App\Tests\Factory;

use App\Entity\Reminder;
use App\Repository\ReminderRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Reminder>
 *
 * @method static         Reminder|Proxy createOne(array $attributes = [])
 * @method static         Reminder[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static         Reminder[]|Proxy[] createSequence(array|callable $sequence)
 * @method static         Reminder|Proxy find(object|array|mixed $criteria)
 * @method static         Reminder|Proxy findOrCreate(array $attributes)
 * @method static         Reminder|Proxy first(string $sortedField = 'id')
 * @method static         Reminder|Proxy last(string $sortedField = 'id')
 * @method static         Reminder|Proxy random(array $attributes = [])
 * @method static         Reminder|Proxy randomOrCreate(array $attributes = [])
 * @method static         Reminder[]|Proxy[] all()
 * @method static         Reminder[]|Proxy[] findBy(array $attributes)
 * @method static         Reminder[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static         Reminder[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static         ReminderRepository|RepositoryProxy repository()
 * @method Reminder|Proxy create(array|callable $attributes = [])
 */
final class ReminderFactory extends ModelFactory
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
            'duration' => self::faker()->randomNumber(),
            'isActive' => true,
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Reminder $reminder): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Reminder::class;
    }
}

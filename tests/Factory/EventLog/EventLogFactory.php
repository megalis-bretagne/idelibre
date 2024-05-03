<?php

namespace App\Tests\Factory\EventLog;

use App\Entity\EventLog\Action;
use App\Entity\EventLog\EventLog;
use App\Repository\EventLogRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<EventLog>
 *
 * @method        EventLog|Proxy                     create(array|callable $attributes = [])
 * @method static EventLog|Proxy                     createOne(array $attributes = [])
 * @method static EventLog|Proxy                     find(object|array|mixed $criteria)
 * @method static EventLog|Proxy                     findOrCreate(array $attributes)
 * @method static EventLog|Proxy                     first(string $sortedField = 'id')
 * @method static EventLog|Proxy                     last(string $sortedField = 'id')
 * @method static EventLog|Proxy                     random(array $attributes = [])
 * @method static EventLog|Proxy                     randomOrCreate(array $attributes = [])
 * @method static EventLogRepository|RepositoryProxy repository()
 * @method static EventLog[]|Proxy[]                 all()
 * @method static EventLog[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static EventLog[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static EventLog[]|Proxy[]                 findBy(array $attributes)
 * @method static EventLog[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static EventLog[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<EventLog> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<EventLog> createOne(array $attributes = [])
 * @phpstan-method static Proxy<EventLog> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<EventLog> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<EventLog> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<EventLog> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<EventLog> random(array $attributes = [])
 * @phpstan-method static Proxy<EventLog> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<EventLog> repository()

 */
final class EventLogFactory extends ModelFactory
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
            'action' => Action::USER_CREATE,
            'authorId' => self::faker()->uuid(),
            'authorName' => self::faker()->userName(),
            'targetId' => self::faker()->uuid(),
            'targetName' => self::faker()->text(255),
            'structureId' => self::faker()->uuid(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(EventLog $eventLog): void {})
        ;
    }

    protected static function getClass(): string
    {
        return EventLog::class;
    }
}

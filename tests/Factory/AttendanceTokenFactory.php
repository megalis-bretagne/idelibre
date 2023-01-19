<?php

namespace App\Tests\Factory;

use App\Entity\AttendanceToken;
use App\Repository\AttendanceTokenRepository;
use App\Tests\Story\ConvocationStory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<AttendanceToken>
 *
 * @method AttendanceToken|Proxy create(array|callable $attributes = [])
 * @method static                AttendanceToken|Proxy createOne(array $attributes = [])
 * @method static                AttendanceToken|Proxy find(object|array|mixed $criteria)
 * @method static                AttendanceToken|Proxy findOrCreate(array $attributes)
 * @method static                AttendanceToken|Proxy first(string $sortedField = 'id')
 * @method static                AttendanceToken|Proxy last(string $sortedField = 'id')
 * @method static                AttendanceToken|Proxy random(array $attributes = [])
 * @method static                AttendanceToken|Proxy randomOrCreate(array $attributes = [])
 * @method static                AttendanceTokenRepository|RepositoryProxy repository()
 * @method static                AttendanceToken[]|Proxy[] all()
 * @method static                AttendanceToken[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static                AttendanceToken[]|Proxy[] createSequence(array|callable $sequence)
 * @method static                AttendanceToken[]|Proxy[] findBy(array $attributes)
 * @method static                AttendanceToken[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static                AttendanceToken[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class AttendanceTokenFactory extends ModelFactory
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
            'convocation' => ConvocationStory::convocationActor1(),
            'expiredAt' => self::faker()->dateTime(),
            'token' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(AttendanceToken $attendanceToken): void {})
        ;
    }

    protected static function getClass(): string
    {
        return AttendanceToken::class;
    }
}

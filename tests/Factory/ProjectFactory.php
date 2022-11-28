<?php

namespace App\Tests\Factory;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Project>
 *
 * @method static        Project|Proxy createOne(array $attributes = [])
 * @method static        Project[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static        Project[]|Proxy[] createSequence(array|callable $sequence)
 * @method static        Project|Proxy find(object|array|mixed $criteria)
 * @method static        Project|Proxy findOrCreate(array $attributes)
 * @method static        Project|Proxy first(string $sortedField = 'id')
 * @method static        Project|Proxy last(string $sortedField = 'id')
 * @method static        Project|Proxy random(array $attributes = [])
 * @method static        Project|Proxy randomOrCreate(array $attributes = [])
 * @method static        Project[]|Proxy[] all()
 * @method static        Project[]|Proxy[] findBy(array $attributes)
 * @method static        Project[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        Project[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        ProjectRepository|RepositoryProxy repository()
 * @method Project|Proxy create(array|callable $attributes = [])
 */
final class ProjectFactory extends ModelFactory
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
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(Project $project): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Project::class;
    }
}

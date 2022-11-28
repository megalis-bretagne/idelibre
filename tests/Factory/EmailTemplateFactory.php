<?php

namespace App\Tests\Factory;

use App\Entity\EmailTemplate;
use App\Repository\EmailTemplateRepository;
use App\Service\Email\EmailData;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<EmailTemplate>
 *
 * @method static              EmailTemplate|Proxy createOne(array $attributes = [])
 * @method static              EmailTemplate[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static              EmailTemplate[]|Proxy[] createSequence(array|callable $sequence)
 * @method static              EmailTemplate|Proxy find(object|array|mixed $criteria)
 * @method static              EmailTemplate|Proxy findOrCreate(array $attributes)
 * @method static              EmailTemplate|Proxy first(string $sortedField = 'id')
 * @method static              EmailTemplate|Proxy last(string $sortedField = 'id')
 * @method static              EmailTemplate|Proxy random(array $attributes = [])
 * @method static              EmailTemplate|Proxy randomOrCreate(array $attributes = [])
 * @method static              EmailTemplate[]|Proxy[] all()
 * @method static              EmailTemplate[]|Proxy[] findBy(array $attributes)
 * @method static              EmailTemplate[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static              EmailTemplate[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static              EmailTemplateRepository|RepositoryProxy repository()
 * @method EmailTemplate|Proxy create(array|callable $attributes = [])
 */
final class EmailTemplateFactory extends ModelFactory
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
            'content' => self::faker()->text(),
            'subject' => self::faker()->text(),
            'isDefault' => false,
            'category' => self::faker()->text(),
            'isAttachment' => true,
            'format' => EmailData::FORMAT_HTML,
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this
            // ->afterInstantiate(function(EmailTemplate $emailTemplate): void {})
        ;
    }

    protected static function getClass(): string
    {
        return EmailTemplate::class;
    }
}

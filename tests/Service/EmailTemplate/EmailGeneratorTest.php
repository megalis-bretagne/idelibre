<?php

namespace App\Tests\Service\EmailTemplate;

use App\DataFixtures\ConvocationFixtures;
use App\DataFixtures\SittingFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\EmailTemplate;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\HtmlTag;
use App\Service\Util\DateUtil;
use App\Service\Util\GenderConverter;
use App\Tests\FindEntityTrait;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailGeneratorTest extends WebTestCase
{

    use FixturesTrait;
    use FindEntityTrait;


    private EntityManagerInterface $entityManager;
    /**
     * @var \App\Service\EmailTemplate\EmailTemplateManager|object|null
     */
    private $emailTemplateManager;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $container = self::$container;
        $this->emailTemplateManager = $container->get('App\Service\EmailTemplate\EmailTemplateManager');

        $this->loadFixtures([
            UserFixtures::class,
            SittingFixtures::class,
            ConvocationFixtures::class
        ]);
    }

    public function testGenerateNotification()
    {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setContent('test de génération de message : #variable#');
        $emailTemplate->setSubject('test de génération de titre : #variable#');
        $generator = new EmailGenerator(new DateUtil(), new GenderConverter(), $this->emailTemplateManager);
        $emailData = $generator->generateFromTemplate($emailTemplate, ['#variable#' => 'test']);
        $this->assertEquals(
            HtmlTag::START_HTML . 'test de génération de message : test' . HtmlTag::END_HTML,
            $emailData->getContent()
        );
        $this->assertEquals(
            'test de génération de titre : test',
            $emailData->getSubject()
        );
    }


    public function testGenerateParams()
    {
        $sitting = $this->getOneSittingBy(['name' => 'Conseil Libriciel']);
        $actor = $this->getOneUserBy(['username' => 'actor1@libriciel.coop']);
        $convocation = $this->getOneConvocationBy(['sitting' => $sitting, 'actor' => $actor]);
        $generator = new EmailGenerator(new DateUtil(), new GenderConverter(), $this->emailTemplateManager);

        $expected = [
            '#typeseance#' => 'Conseil Libriciel',
            '#dateseance#' => '22/10/2020',
            '#heureseance#' => '02:00',
            '#lieuseance#' => 'Salle du conseil',
            '#prenom#' => 'actor_1',
            '#nom#' => 'libriciel',
            '#username#' => 'actor1@libriciel.coop',
            '#titre#' => 'Madame le maire',
            '#civilite#' => 'Monsieur'];

        $this->assertEquals($expected, $generator->generateParams($convocation));

    }
}

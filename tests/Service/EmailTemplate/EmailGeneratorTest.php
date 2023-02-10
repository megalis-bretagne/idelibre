<?php

namespace App\Tests\Service\EmailTemplate;

use App\Entity\EmailTemplate;
use App\Service\EmailTemplate\EmailGenerator;
use App\Service\EmailTemplate\EmailTemplateManager;
use App\Service\Util\DateUtil;
use App\Service\Util\GenderConverter;
use App\Tests\Factory\AttendanceTokenFactory;
use App\Tests\Factory\ConvocationFactory;
use App\Tests\Factory\EmailTemplateFactory;
use App\Tests\FindEntityTrait;
use App\Tests\Story\ConvocationStory;
use App\Tests\Story\SittingStory;
use App\Tests\Story\StructureStory;
use App\Tests\Story\TypeStory;
use App\Tests\Story\UserStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmailGeneratorTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;
    use FindEntityTrait;

    private ?KernelBrowser $kernelBrowser;
    private EntityManagerInterface $entityManager;
    private ?EmailTemplateManager $emailTemplateManager;
    private ?EmailGenerator $emailGenerator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->emailTemplateManager = self::getContainer()->get(EmailTemplateManager::class);
        $this->params = self::getContainer()->getParameterBag();
        $this->router = self::getContainer()->get(RouterInterface::class);
        $this->emailGenerator = self::getContainer()->get(EmailGenerator::class);

        self::ensureKernelShutdown();

        UserStory::load();
        StructureStory::load();
        ConvocationStory::load();
    }

    public function testGenerateNotification()
    {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setContent('test de génération de message : #variable#');
        $emailTemplate->setSubject('test de génération de titre : #variable#');

        $generator = new EmailGenerator(
            new DateUtil(),
            new GenderConverter(),
            $this->emailTemplateManager,
            $this->params,
            $this->router
        );

        $emailData = $generator->generateFromTemplate($emailTemplate, ['#variable#' => 'test']);

        $this->assertEquals(
            'test de génération de message : test',
            $emailData->getContent()
        );

        $this->assertEquals(
            'test de génération de titre : test',
            $emailData->getSubject()
        );
    }

    public function testGenerateParams()
    {
        $convocation = ConvocationStory::convocationActor2SentWithToken();
        AttendanceTokenFactory::createOne([
            'token' => 'mytoken',
            'convocation' => $convocation,
        ]);

        $generator = new EmailGenerator(
            new DateUtil(),
            new GenderConverter(),
            $this->emailTemplateManager,
            $this->params,
            $this->router,
        );

        $expected = [
            '#typeseance#' => 'Conseil',
            '#dateseance#' => '22/10/2020',
            '#heureseance#' => '02:00',
            '#lieuseance#' => 'Agora',
            '#prenom#' => 'actor_1',
            '#nom#' => 'libriciel',
            '#username#' => 'actor1@libriciel',
            '#titre#' => 'Madame le maire',
            '#civilite#' => 'Monsieur',
            '#urlseance#' => 'idelibre-test.libriciel.fr/idelibre_client',
            '#mandataire#' => null,
            '#presence#' => null,
            '#urlpresence#' => $this->emailGenerator->generateAttendanceUrl($convocation->getAttendanceToken()->getToken()),
        ];

        $this->assertEquals($expected, $generator->generateParams($convocation->object()));
    }


    public function testGenerateSittingParams()
    {

        $sitting = SittingStory::sittingConseilLibriciel();

        $generator = new EmailGenerator(
            new DateUtil(),
            new GenderConverter(),
            $this->emailTemplateManager,
            $this->params,
            $this->router,
        );

        $expected = [
            '#typeseance#' => 'Conseil Libriciel',
            '#dateseance#' => '22/10/2020',
            '#heureseance#' => '02:00',
            '#lieuseance#' => 'Salle du conseil',
            '#urlseance#' => 'idelibre-test.libriciel.fr/idelibre_client',
        ];

        $this->assertEquals($expected, $generator->generateSittingParams($sitting->object()));
    }

    public function testGenerateEmailTemplateSubject()
    {
        $sitting = SittingStory::sittingConseilLibriciel();
        $emailTemplate = EmailTemplateFactory::new([
            'structure' => StructureStory::libriciel(),
            'type' => TypeStory::typeConseilLibriciel(),
            'name' => 'Conseil Libriciel ',
            'subject' => 'idelibre : une nouvelle convocation pour le type #typeseance#',
            'category' => EmailTemplate::CATEGORY_CONVOCATION,
            'content' => 'Voici mon template pour les seance de type conseil de la structure libriciel',
        ])->create();

        $generator = new EmailGenerator(
            new DateUtil(),
            new GenderConverter(),
            $this->emailTemplateManager,
            $this->params,
            $this->router,
        );

        $expected = 'idelibre : une nouvelle convocation pour le type Conseil Libriciel';

        $this->assertEquals($expected, $generator->generateEmailTemplateSubject($sitting->object(), $emailTemplate->getSubject()) );

    }
}

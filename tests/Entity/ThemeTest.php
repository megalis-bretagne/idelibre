<?php

namespace App\Tests\Entity;

use App\Entity\Structure;
use App\Entity\Theme;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ThemeTest extends WebTestCase
{
    use HasValidationError;
    use StringTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function testValid()
    {
        $theme = (new Theme())
            ->setStructure(new Structure())
            ->setName('my new file.pdf');

        $this->assertHasValidationErrors($theme, 0);
    }

    public function testInvalidEmptyName()
    {
        $theme = (new Theme())
            ->setStructure(new Structure())
            ->setName('');

        $this->assertHasValidationErrors($theme, 1);
    }

    public function testInvalidNoName()
    {
        $theme = (new Theme())
            ->setStructure(new Structure());

        $this->assertHasValidationErrors($theme, 1);
    }

    public function testInvalidNameTooLong()
    {
        $theme = (new Theme())
            ->setStructure(new Structure())
            ->setName($this->genString(256));

        $this->assertHasValidationErrors($theme, 1);
    }

    public function testInvalidNoStructure()
    {
        $theme = (new Theme())
            ->setName('my new file.pdf');

        $this->assertHasValidationErrors($theme, 1);
    }
}

<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Entity\Project;
use App\Entity\Sitting;
use App\Tests\HasValidationError;
use App\Tests\StringTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectTest extends WebTestCase
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
        $project = (new Project())
            ->setName('My awesome project')
            ->setSitting(new Sitting())
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 0);
    }

    public function testInvalidEmptyName()
    {
        $project = (new Project())
            ->setName('')
            ->setSitting(new Sitting())
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 1);
    }

    public function testInvalidNameTooLong()
    {
        $project = (new Project())
            ->setName($this->genString(515))
            ->setSitting(new Sitting())
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 1);
    }

    public function testInvalidNoName()
    {
        $project = (new Project())
            ->setSitting(new Sitting())
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 1);
    }

    public function testInvalidNoRank()
    {
        $project = (new Project())
            ->setName('name')
            ->setSitting(new Sitting())
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 1);
    }

    public function testInvalidNoSitting()
    {
        $project = (new Project())
            ->setName('My awesome project')
            ->setRank(1)
            ->setFile(new File());

        $this->assertHasValidationErrors($project, 1);
    }

    public function testInvalidNoFile()
    {
        $project = (new Project())
            ->setName('My awesome project')
            ->setSitting(new Sitting())
            ->setRank(1);

        $this->assertHasValidationErrors($project, 1);
    }
}

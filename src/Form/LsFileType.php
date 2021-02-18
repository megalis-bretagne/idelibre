<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class LsFileType extends AbstractType
{
    public function getParent(): ?string
    {
        return FileType::class;
    }
}

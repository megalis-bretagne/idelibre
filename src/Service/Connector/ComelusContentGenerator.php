<?php

namespace App\Service\Connector;

use App\Entity\Sitting;
use App\Service\EmailTemplate\TemplateTag;
use App\Service\Util\DateUtil;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class ComelusContentGenerator
{
    public function __construct(
        private DateUtil $dateUtil,
    ) {
    }

    public function createDescription(string $description, Sitting $sitting): string
    {
        return $this->generate($description, $this->getParams($sitting));
    }

    #[ArrayShape([
        TemplateTag::SITTING_TYPE => 'null|string',
        TemplateTag::SITTING_DATE => 'string',
        TemplateTag::SITTING_TIME => 'string',
        TemplateTag::SITTING_PLACE => 'string',
    ])]
    private function getParams(Sitting $sitting): array
    {
        return [
            TemplateTag::SITTING_TYPE => $sitting->getName(),
            TemplateTag::SITTING_DATE => $this->dateUtil->getFormattedDate(
                $sitting->getDate(),
                $sitting->getStructure()->getTimezone()->getName()
            ),
            TemplateTag::SITTING_TIME => $this->dateUtil->getFormattedTime(
                $sitting->getDate(),
                $sitting->getStructure()->getTimezone()->getName()
            ),
            TemplateTag::SITTING_PLACE => $sitting->getPlace() ?? '',
        ];
    }

    private function generate(string $content, array $params): string
    {
        return strtr($content, $params);
    }
}

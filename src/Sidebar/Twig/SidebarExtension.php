<?php

namespace App\Sidebar\Twig;

use App\Sidebar\State\SidebarState;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension
{
    private SidebarState $sidebarState;
    private Environment $twig;

    public function __construct(Environment $twig, SidebarState $sidebarState)
    {
        $this->sidebarState = $sidebarState;
        $this->twig = $twig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ls_sidebar', [$this, 'getSidebar'], ['is_safe' => ['html']]),
        ];
    }

    public function getSidebar(): string
    {
        return $this->twig->render('sidebar/sidebar.twig.html.twig', [
            'activeNavs' => $this->sidebarState->getActiveNavs(),
        ]);
    }
}

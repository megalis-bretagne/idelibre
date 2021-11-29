<?php

namespace App\Sidebar\State;

class SidebarState
{
    private array $activeNavs = [];

    public function getActiveNavs(): array
    {
        return $this->activeNavs;
    }

    public function setActiveNavs(array $activeNavs): SidebarState
    {
        $this->activeNavs = $activeNavs;

        return $this;
    }
}

<?php

namespace App\View\Components;

use App\Services\FormThemeService;
use Illuminate\View\Component;
use Illuminate\View\View;

class PublicLayout extends Component
{
    public array $themeData;
    public string $themeStyles;

    public function __construct(
        public ?string $theme = 'indigo'
    ) {
        $this->themeData = FormThemeService::get($this->theme);
        $this->themeStyles = FormThemeService::getCssVariables($this->theme);
    }

    public function render(): View
    {
        return view('layouts.public');
    }
}

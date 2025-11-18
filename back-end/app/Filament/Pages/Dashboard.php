<?php

namespace App\Filament\Pages;

use App\Models\Document;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }
}

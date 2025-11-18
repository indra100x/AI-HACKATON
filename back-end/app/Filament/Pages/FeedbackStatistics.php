<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ApprovedFeedbackChart;
use App\Filament\Widgets\RequestsPerDayChart;
use App\Models\Feedback;
use Filament\Pages\Page;

class FeedbackStatistics extends Page
{
    protected string $view = 'filament.pages.feedback-statistics';

    protected static ?string $title = 'Feedback Statistics';

    protected static ?string $navigationLabel = 'Statistics';

    protected static ?int $navigationSort = 2;

    public function getStats(): array
    {
        $approvedFeedback = Feedback::where('approved', true)->get();

        $positive = $approvedFeedback->where('rating', 'positive')->count();
        $negative = $approvedFeedback->where('rating', 'negative')->count();

        return [
            'positive' => $positive,
            'negative' => $negative,
            'total' => $positive + $negative,
        ];
    }

    public function getWidgets(): array
    {
        return [
            ApprovedFeedbackChart::class,
            RequestsPerDayChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}

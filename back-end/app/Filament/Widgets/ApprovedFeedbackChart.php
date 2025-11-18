<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class ApprovedFeedbackChart extends LineChartWidget
{
    protected ?string $heading = 'Approved Feedback Trend';

    protected function getData(): array
    {
        $positiveData = Feedback::where('approved', true)
            ->where('rating', 'positive')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $negativeData = Feedback::where('approved', true)
            ->where('rating', 'negative')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $allDates = collect($positiveData->keys())->merge($negativeData->keys())->unique()->sort();

        $labels = $allDates->map(fn($date) => date('M d', strtotime($date)))->toArray();
        $positiveValues = $allDates->map(fn($date) => $positiveData->get($date, 0))->toArray();
        $negativeValues = $allDates->map(fn($date) => $negativeData->get($date, 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Positive Ratings',
                    'data' => $positiveValues,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Negative Ratings',
                    'data' => $negativeValues,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

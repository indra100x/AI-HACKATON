<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class RequestsPerDayChart extends LineChartWidget
{
    protected ?string $heading = 'Requests Per Day';

    protected function getData(): array
    {
        $requests = DB::table('feedbacks')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = $requests->pluck('date')->map(fn($date) => date('M d', strtotime($date)))->toArray();
        $data = $requests->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Daily Requests',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

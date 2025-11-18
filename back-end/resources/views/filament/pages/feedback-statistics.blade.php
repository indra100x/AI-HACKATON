<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $percentage = $stats['total'] > 0 ? round(($stats['positive'] / $stats['total']) * 100) : 0;
    @endphp

    <div class="space-y-6">
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <!-- Positive Card -->
            <div class="overflow-hidden rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-green-100 p-6 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Positive Feedback</p>
                        <p class="mt-2 text-4xl font-bold text-green-600">{{ $stats['positive'] }}</p>
                        <p class="mt-1 text-xs text-green-700">{{ $percentage }}% of total</p>
                    </div>
                    <div class="text-5xl text-green-200 opacity-20">👍</div>
                </div>
            </div>

            <!-- Negative Card -->
            <div class="overflow-hidden rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-red-100 p-6 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Negative Feedback</p>
                        <p class="mt-2 text-4xl font-bold text-red-600">{{ $stats['negative'] }}</p>
                        <p class="mt-1 text-xs text-red-700">{{ 100 - $percentage }}% of total</p>
                    </div>
                    <div class="text-5xl text-red-200 opacity-20">👎</div>
                </div>
            </div>

            <!-- Total Card -->
            <div class="overflow-hidden rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Approved</p>
                        <p class="mt-2 text-4xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                        <p class="mt-1 text-xs text-blue-700">Approved feedbacks</p>
                    </div>
                    <div class="text-5xl text-blue-200 opacity-20">✓</div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Line Plot -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
                <h2 class="mb-6 text-lg font-semibold text-gray-800">Approved Feedback Trend</h2>
                <canvas id="feedbackLinePlot"></canvas>
            </div>

            <!-- Requests by Day Plot -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
                <h2 class="mb-6 text-lg font-semibold text-gray-800">Server Requests per Day</h2>
                <canvas id="requestsStatPlot"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Line Plot
        const lineCtx = document.getElementById('feedbackLinePlot').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Positive', 'Negative'],
                datasets: [{
                    label: 'Approved Feedback Count',
                    data: [{{ $stats['positive'] }}, {{ $stats['negative'] }}],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 8,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: 'rgb(255, 255, 255)',
                    pointBorderWidth: 2,
                    pointHoverRadius: 10,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: { size: 13, weight: 'bold' },
                            padding: 15,
                        }
                    },
                    tooltip: {
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12, weight: 'bold' },
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12, weight: 'bold' },
                        },
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });

        // Requests by Day Plot
        @php
            $requestsData = \App\Models\Document::select(
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )->groupBy('day')->orderBy('day')->pluck('count', 'day')->toArray();
        @endphp
        const requestsData = @json($requestsData);

        const requestDays = Object.keys(requestsData);
        const requestCounts = Object.values(requestsData);

        const requestsCtx = document.getElementById('requestsStatPlot').getContext('2d');
        new Chart(requestsCtx, {
            type: 'line',
            data: {
                labels: requestDays,
                datasets: [{
                    label: 'Requests per Day',
                    data: requestCounts,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 8,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: 'rgb(255, 255, 255)',
                    pointBorderWidth: 2,
                    pointHoverRadius: 10,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: { size: 13, weight: 'bold' },
                            padding: 15,
                        }
                    },
                    tooltip: {
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12, weight: 'bold' },
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12, weight: 'bold' },
                        },
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });
    </script>
</x-filament-panels::page>

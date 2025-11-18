<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
            <!-- Total Documents Card -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Documents</p>
                        <p class="mt-2 text-4xl font-bold text-blue-600">{{ \App\Models\Document::count() }}</p>
                    </div>
                    <div class="text-5xl text-blue-200 opacity-20">📄</div>
                </div>
            </div>

            <!-- Total Requests Card -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-purple-50 to-purple-100 p-6 shadow-md hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Requests</p>
                        <p class="mt-2 text-4xl font-bold text-purple-600">{{ \App\Models\Document::count() }}</p>
                    </div>
                    <div class="text-5xl text-purple-200 opacity-20">📊</div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Line Plot -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
                <h2 class="mb-6 text-lg font-semibold text-gray-800">Documents Over Time</h2>
                <canvas id="linePlot"></canvas>
            </div>

            <!-- Requests Plot -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md">
                <h2 class="mb-6 text-lg font-semibold text-gray-800">Requests by Day</h2>
                <canvas id="requestsPlot"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        @php
            $decisions = \App\Models\Document::select(
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'),
                'decision'
            )->groupBy('decision')->pluck('count', 'decision')->toArray();

            $requestsData = \App\Models\Document::select(
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )->groupBy('day')->orderBy('day')->pluck('count', 'day')->toArray();
        @endphp

        const decisions = @json($decisions);
        const requestsData = @json($requestsData);

        // Line Plot
        const lineCtx = document.getElementById('linePlot').getContext('2d');
        const labels = Object.keys(decisions).map(d => d.charAt(0).toUpperCase() + d.slice(1));
        const data = Object.values(decisions);
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Document Count',
                    data: data,
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

        // Requests by Day Scatter Plot (x=day, y=requests)
        const requestDays = Object.keys(requestsData);
        const requestCounts = Object.values(requestsData);

        // Convert to scatter plot format: {x: dayIndex, y: count}
        const scatterData = requestDays.map((day, index) => ({
            x: index + 1,
            y: requestCounts[index]
        }));

        const requestsCtx = document.getElementById('requestsPlot').getContext('2d');
        new Chart(requestsCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Requests per Day',
                    data: scatterData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    pointRadius: 8,
                    pointHoverRadius: 10,
                    showLine: false,
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
                        callbacks: {
                            title: function(context) {
                                return 'Day ' + context[0].parsed.x;
                            },
                            label: function(context) {
                                return 'Requests: ' + context.parsed.y;
                            }
                        }
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
                        type: 'linear',
                        ticks: {
                            font: { size: 12, weight: 'bold' },
                            callback: function(value) {
                                const dayIndex = value - 1;
                                if (dayIndex >= 0 && dayIndex < requestDays.length) {
                                    return requestDays[dayIndex];
                                }
                                return '';
                            }
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

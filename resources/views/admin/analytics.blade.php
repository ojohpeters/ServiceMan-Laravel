@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.css" rel="stylesheet">
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    .mini-chart {
        height: 200px;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
            <p class="text-gray-600 mt-2 text-sm sm:text-base">Comprehensive insights and performance metrics</p>
        </div>
        
        <!-- Period Selector -->
        <div class="flex gap-2 bg-white rounded-lg shadow-md p-1 border border-gray-200">
            <a href="{{ route('admin.analytics', ['period' => 'month']) }}" 
               class="px-4 py-2 rounded-md text-sm font-semibold transition-all {{ $period === 'month' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                This Month
            </a>
            <a href="{{ route('admin.analytics', ['period' => 'year']) }}" 
               class="px-4 py-2 rounded-md text-sm font-semibold transition-all {{ $period === 'year' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                This Year
            </a>
            <a href="{{ route('admin.analytics', ['period' => 'all']) }}" 
               class="px-4 py-2 rounded-md text-sm font-semibold transition-all {{ $period === 'all' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                All Time
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Revenue Card -->
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <span class="text-blue-100 text-xs sm:text-sm font-semibold">{{ ucfirst($period) }}</span>
            </div>
            <p class="text-blue-100 text-xs sm:text-sm font-medium mb-1">Revenue</p>
            <p class="text-2xl sm:text-3xl font-bold">₦{{ number_format($revenue, 0) }}</p>
            <p class="text-blue-100 text-xs mt-2">Total: ₦{{ number_format($totalRevenue, 0) }}</p>
        </div>

        <!-- Transactions Card -->
        <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-credit-card text-2xl"></i>
                </div>
                <span class="text-green-100 text-xs sm:text-sm font-semibold">{{ ucfirst($period) }}</span>
            </div>
            <p class="text-green-100 text-xs sm:text-sm font-medium mb-1">Transactions</p>
            <p class="text-2xl sm:text-3xl font-bold">{{ number_format($transactionsCount) }}</p>
            <p class="text-green-100 text-xs mt-2">Total: {{ number_format($totalTransactions) }}</p>
        </div>

        <!-- Service Requests Card -->
        <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <span class="text-purple-100 text-xs sm:text-sm font-semibold">Total</span>
            </div>
            <p class="text-purple-100 text-xs sm:text-sm font-medium mb-1">Service Requests</p>
            <p class="text-2xl sm:text-3xl font-bold">{{ number_format($serviceRequestStats['total']) }}</p>
            <p class="text-purple-100 text-xs mt-2">{{ number_format($completionRate, 1) }}% Completion Rate</p>
        </div>

        <!-- Average Rating Card -->
        <div class="stat-card bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl shadow-lg p-5 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <span class="text-yellow-100 text-xs sm:text-sm font-semibold">Average</span>
            </div>
            <p class="text-yellow-100 text-xs sm:text-sm font-medium mb-1">Rating</p>
            <div class="flex items-center gap-2">
                <p class="text-2xl sm:text-3xl font-bold">{{ $ratingStats['average'] }}</p>
                <div class="flex">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($ratingStats['average']) ? '' : 'text-yellow-200' }} text-sm"></i>
                    @endfor
                </div>
            </div>
            <p class="text-yellow-100 text-xs mt-2">{{ number_format($ratingStats['total_ratings']) }} Total Ratings</p>
        </div>
    </div>

    <!-- Charts Row 1: Revenue & Status Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Request Status Distribution</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Category Performance & Rating Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Category Performance -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Categories by Requests</h3>
            <div class="chart-container mini-chart">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 space-y-2 max-h-48 overflow-y-auto">
                @foreach($categoryStats->take(5) as $category)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $category->name }}</p>
                            <p class="text-xs text-gray-500">{{ $category->completed_requests }} completed of {{ $category->total_requests }} total</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-blue-600">₦{{ number_format($category->revenue ?? 0, 0) }}</p>
                            <p class="text-xs text-gray-500">{{ $category->active_servicemen_count ?? 0 }} servicemen</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Rating Distribution -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Rating Distribution</h3>
            <div class="chart-container mini-chart">
                <canvas id="ratingChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-5 gap-2">
                <div class="text-center">
                    <p class="text-lg font-bold text-yellow-500">5★</p>
                    <p class="text-xs text-gray-600">{{ $ratingStats['five_star'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-yellow-400">4★</p>
                    <p class="text-xs text-gray-600">{{ $ratingStats['four_star'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-400">3★</p>
                    <p class="text-xs text-gray-600">{{ $ratingStats['three_star'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-400">2★</p>
                    <p class="text-xs text-gray-600">{{ $ratingStats['two_star'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-gray-400">1★</p>
                    <p class="text-xs text-gray-600">{{ $ratingStats['one_star'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid: Service Requests & Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Service Request Statistics -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Service Request Statistics</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Total Requests</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($serviceRequestStats['total']) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Pending Assignment</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($serviceRequestStats['pending_assignment']) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">In Progress</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($serviceRequestStats['in_progress']) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Completed</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($serviceRequestStats['completed']) }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Cancelled</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($serviceRequestStats['cancelled']) }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Emergency</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($serviceRequestStats['emergency']) }}</p>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">User Statistics</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Total Users</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($userStats['total_users']) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Clients</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($userStats['clients']) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Servicemen</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($userStats['servicemen']) }}</p>
                </div>
                <div class="bg-teal-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Active Servicemen</p>
                    <p class="text-2xl font-bold text-teal-600">{{ number_format($userStats['active_servicemen']) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">Verified Users</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($userStats['verified_users']) }}</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600 mb-1">New This Month</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($userStats['new_this_month']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Servicemen -->
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6 sm:mb-8">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Top Performing Servicemen</h3>
        @if($topServicemen->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jobs</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topServicemen as $index => $serviceman)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('servicemen.show', $serviceman['id']) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                        {{ $serviceman['name'] }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $serviceman['category'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-semibold text-gray-900 mr-1">{{ $serviceman['rating'] }}</span>
                                        <div class="flex">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($serviceman['rating']) ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ number_format($serviceman['jobs_completed']) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-green-600">₦{{ number_format($serviceman['revenue'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 py-8">No servicemen data available yet</p>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Activity</h3>
        @if($recentActivity->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($recentActivity as $activity)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0 mt-1">
                            @if(str_contains($activity['type'], 'PAYMENT'))
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-green-600 text-sm"></i>
                                </div>
                            @elseif(str_contains($activity['type'], 'COMPLETED'))
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-check-circle text-blue-600 text-sm"></i>
                                </div>
                            @elseif(str_contains($activity['type'], 'ASSIGN'))
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <i class="fas fa-user-plus text-purple-600 text-sm"></i>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-bell text-gray-600 text-sm"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $activity['message'] }}</p>
                        </div>
                        <div class="flex-shrink-0 text-xs text-gray-500">
                            {{ $activity['created_at'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 py-8">No recent activity</p>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($monthlyRevenueData);
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.label),
            datasets: [{
                label: 'Revenue (₦)',
                data: revenueData.map(d => d.revenue),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₦' + new Intl.NumberFormat().format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + new Intl.NumberFormat().format(value);
                        }
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusDistribution);
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(147, 51, 234, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = @json($categoryStats->take(5));
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.map(c => c.name),
            datasets: [{
                label: 'Total Requests',
                data: categoryData.map(c => c.total_requests),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingChart').getContext('2d');
    @php
        $ratingDataArray = [
            $ratingStats['five_star'] ?? 0,
            $ratingStats['four_star'] ?? 0,
            $ratingStats['three_star'] ?? 0,
            $ratingStats['two_star'] ?? 0,
            $ratingStats['one_star'] ?? 0
        ];
    @endphp
    const ratingData = @json($ratingDataArray);
    new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: ['5★', '4★', '3★', '2★', '1★'],
            datasets: [{
                label: 'Number of Ratings',
                data: ratingData,
                backgroundColor: [
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(251, 191, 36, 0.6)',
                    'rgba(156, 163, 175, 0.6)',
                    'rgba(156, 163, 175, 0.6)',
                    'rgba(156, 163, 175, 0.6)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-file-alt mr-3 text-blue-600"></i>
                        System Logs
                    </h1>
                    <p class="text-gray-600 mt-1">View and debug application errors and events</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.logs.download') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Download Log
                    </a>
                    <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all logs?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Clear Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.logs') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                    <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Levels</option>
                        <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
                        <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>Debug</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lines to Show</label>
                    <select name="lines" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="100" {{ request('lines', 100) == 100 ? 'selected' : '' }}>Last 100</option>
                        <option value="200" {{ request('lines') == 200 ? 'selected' : '' }}>Last 200</option>
                        <option value="500" {{ request('lines') == 500 ? 'selected' : '' }}>Last 500</option>
                        <option value="1000" {{ request('lines') == 1000 ? 'selected' : '' }}>Last 1000</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search in logs..." 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        @if(request()->hasAny(['level', 'search', 'lines']))
                            <a href="{{ route('admin.logs') }}" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Log Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">Total Log Entries</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($totalLines ?? 0) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">Showing</div>
                <div class="text-2xl font-bold text-gray-900">{{ count($entries) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">Filtered Level</div>
                <div class="text-2xl font-bold text-gray-900">{{ $filters['level'] ?? 'All' }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">Log File Size</div>
                <div class="text-2xl font-bold text-gray-900">
                    @php
                        $logFile = storage_path('logs/laravel.log');
                        $size = file_exists($logFile) ? filesize($logFile) : 0;
                        $sizeInMB = round($size / 1024 / 1024, 2);
                    @endphp
                    {{ $sizeInMB }} MB
                </div>
            </div>
        </div>

        <!-- Log Entries -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            @if(count($entries) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entries as $index => $entry)
                                <tr class="hover:bg-gray-50" x-data="{ expanded: false }">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry['date'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $level = strtolower($entry['level'] ?? 'info');
                                            $colors = [
                                                'error' => 'bg-red-100 text-red-800',
                                                'warning' => 'bg-yellow-100 text-yellow-800',
                                                'info' => 'bg-blue-100 text-blue-800',
                                                'debug' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $color = $colors[$level] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                            {{ strtoupper($entry['level'] ?? 'INFO') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-md truncate">
                                            {{ Str::limit($entry['message'] ?? 'No message', 150) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button @click="expanded = !expanded" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                            <span x-text="expanded ? 'Hide' : 'Details'"></span>
                                        </button>
                                    </td>
                                </tr>
                                <tr x-show="expanded" x-collapse>
                                    <td colspan="4" class="px-6 py-4 bg-gray-50">
                                        <div class="space-y-3">
                                            <div>
                                                <strong>Full Message:</strong>
                                                <pre class="mt-2 p-3 bg-gray-800 text-green-400 rounded-lg overflow-x-auto text-xs">{{ $entry['message'] ?? 'No message' }}</pre>
                                            </div>
                                            @if(isset($entry['context']) && $entry['context'])
                                                <div>
                                                    <strong>Context:</strong>
                                                    <pre class="mt-2 p-3 bg-gray-800 text-green-400 rounded-lg overflow-x-auto text-xs">{{ json_encode($entry['context'], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                            @if(isset($entry['stack']) && $entry['stack'])
                                                <div>
                                                    <strong>Stack Trace:</strong>
                                                    <pre class="mt-2 p-3 bg-gray-800 text-red-400 rounded-lg overflow-x-auto text-xs max-h-96 overflow-y-auto">{{ $entry['stack'] }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No log entries found</p>
                    @if(request()->hasAny(['level', 'search']))
                        <p class="text-gray-400 text-sm mt-2">Try adjusting your filters</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Real-time Log Viewing Tip -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                <div>
                    <h4 class="font-semibold text-blue-900 mb-2">💡 Pro Tip: Real-time Log Viewing</h4>
                    <p class="text-sm text-blue-800 mb-2">
                        For real-time log viewing in your terminal, use Laravel Pail (already installed):
                    </p>
                    <code class="block bg-blue-900 text-blue-100 px-4 py-2 rounded text-sm">
                        php artisan pail
                    </code>
                    <p class="text-xs text-blue-700 mt-2">
                        This shows logs in real-time as they happen. Press Ctrl+C to stop.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection


@extends('layouts.app')

@section('title', 'Services - ServiceMan')
@section('description', 'Browse our professional service categories and find the right expert for your needs.')

@section('content')
<div x-data="servicesPage()" class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative bg-blue-700 text-white py-12 sm:py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center mb-3 sm:mb-4 px-3 sm:px-4 py-1.5 sm:py-2 bg-white/10 rounded-full text-xs sm:text-sm font-semibold">
                    <i class="fas fa-check-circle mr-1.5 sm:mr-2 text-xs sm:text-sm"></i>
                    <span class="hidden xs:inline">Verified Professionals</span>
                    <span class="xs:hidden">Verified</span>
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 sm:mb-6 leading-tight px-2">
                    Find Your Perfect Service Professional
                </h1>
                <p class="text-base sm:text-lg md:text-xl text-blue-50 mb-6 sm:mb-8 leading-relaxed max-w-3xl mx-auto px-4">
                    Connect with skilled, verified professionals ready to help with your home and business needs
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto px-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            @input="filterServices()"
                            placeholder="Search services..."
                            class="w-full pl-11 sm:pl-14 pr-10 sm:pr-12 py-3 sm:py-4 text-gray-900 rounded-xl sm:rounded-2xl shadow-2xl focus:outline-none focus:ring-2 sm:focus:ring-4 focus:ring-blue-300 text-sm sm:text-base md:text-lg placeholder:text-gray-500"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 sm:pl-5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-base sm:text-xl"></i>
                        </div>
                        <button 
                            @click="clearSearch()"
                            x-show="searchQuery"
                            class="absolute inset-y-0 right-0 pr-3 sm:pr-5 flex items-center"
                        >
                            <i class="fas fa-times text-gray-400 hover:text-gray-600 transition-colors text-base sm:text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 gap-3 sm:gap-4 md:gap-6 mt-8 sm:mt-10 md:mt-12 max-w-xl mx-auto px-4">
                    <div class="bg-white/10 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-white/20">
                        <div class="text-2xl sm:text-3xl font-bold mb-1" x-text="totalCategories"></div>
                        <div class="text-xs sm:text-sm text-blue-50 leading-tight">Categories</div>
                    </div>
                    <div class="bg-white/10 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-white/20">
                        <div class="text-2xl sm:text-3xl font-bold mb-1" x-text="totalServicemen"></div>
                        <div class="text-xs sm:text-sm text-blue-50 leading-tight">Professionals</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Wave Decoration -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-12 sm:h-14 md:h-16 text-white">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="currentColor"/>
            </svg>
        </div>
    </section>

    <!-- Services Grid Section -->
    <section class="py-8 sm:py-12 md:py-16 -mt-4 sm:-mt-6 md:-mt-8 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-8 sm:mb-10 md:mb-12">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-4 px-4">
                    Our Service Categories
                </h2>
                <p class="text-sm sm:text-base md:text-lg text-gray-600 max-w-2xl mx-auto px-4">
                    Browse our comprehensive range of professional services. Each category is staffed with verified, skilled professionals ready to help.
                </p>
            </div>

            <!-- Category Filter Dropdown -->
            <div class="flex justify-center mb-6 sm:mb-8 md:mb-10 px-4">
                <div class="relative w-full max-w-xs sm:max-w-sm">
                    <select 
                        x-model="selectedCategory"
                        @change="filterServices()"
                        class="appearance-none w-full pl-4 pr-10 py-3 sm:py-3.5 border border-gray-300 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white cursor-pointer text-sm sm:text-base shadow-md hover:shadow-lg transition-shadow"
                    >
                        <option value="">All Categories</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.name"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8 px-4 sm:px-0" x-show="filteredCategories.length > 0 || searchQuery === '' && activeFilter === 'all'">
                @forelse($categories as $category)
                    <div 
                        x-show="isVisible({{ $category->id }})"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        class="group bg-gray-50 rounded-xl sm:rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200 transform hover:-translate-y-1 sm:hover:-translate-y-2"
                    >
                        <!-- Category Header -->
                        @php
                            $colorClasses = [
                                'bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-indigo-500', 'bg-teal-500'
                            ];
                            $colorIndex = array_search($category->name, $categories->pluck('name')->toArray()) % count($colorClasses);
                            $selectedColorClass = $colorClasses[$colorIndex];
                        @endphp
                        <div class="h-32 sm:h-36 {{ $selectedColorClass }} relative overflow-hidden">
                            <!-- Icon -->
                            <div class="relative h-full flex items-center justify-center">
                                <div class="bg-white/10 rounded-xl p-4 sm:p-5 transform group-hover:scale-110 transition-all duration-300">
                                    <i class="fas fa-{{ $category->icon ?? 'tools' }} text-white text-2xl sm:text-3xl md:text-4xl"></i>
                                </div>
                            </div>
                            
                            <!-- Popular Badge -->
                            @if($category->servicemen_count >= 30)
                                <div class="absolute top-2 sm:top-3 right-2 sm:right-3 bg-yellow-500 text-white px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-bold flex items-center gap-1 shadow-md">
                                    <i class="fas fa-fire text-xs"></i>
                                    <span class="hidden sm:inline">Popular</span>
                                </div>
                            @endif
                        </div>

                        <!-- Category Content -->
                        <div class="p-4 sm:p-5 md:p-6">
                            <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                {{ $category->name }}
                            </h3>
                            
                            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-5 line-clamp-2 min-h-[2.5rem] sm:min-h-[3rem]">
                                {{ $category->description ?? 'Professional ' . strtolower($category->name) . ' services from verified experts' }}
                            </p>
                            
                            <!-- Stats -->
                            <div class="flex items-center justify-between mb-4 sm:mb-5 md:mb-6 pb-4 sm:pb-5 md:pb-6 border-b border-gray-200 gap-2 sm:gap-4">
                                <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-gray-700 flex-1">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user-friends text-blue-700 text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 text-xs sm:text-sm">{{ (int)($category->servicemen_count ?? 0) }}</div>
                                        <div class="text-xs text-gray-600 truncate">Professionals</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <a 
                                    href="{{ route('services.category', $category) }}"
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition-colors text-center group/item"
                                >
                                    <i class="fas fa-eye mr-1.5 sm:mr-2 group-hover/item:translate-x-1 transition-transform inline-block text-xs sm:text-sm"></i>
                                    <span class="hidden sm:inline">View Details</span>
                                    <span class="sm:hidden">View</span>
                                </a>
                                @auth
                                    <a 
                                        href="{{ route('services.category', $category) }}"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition-all shadow-md hover:shadow-lg text-center group/item"
                                    >
                                        <i class="fas fa-calendar-check mr-1.5 sm:mr-2 group-hover/item:translate-x-1 transition-transform inline-block text-xs sm:text-sm"></i>
                                        <span class="hidden sm:inline">Book Now</span>
                                        <span class="sm:hidden">Book</span>
                                    </a>
                                @else
                                    <a 
                                        href="{{ route('login') }}?redirect={{ urlencode(route('services.category', $category)) }}"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition-all shadow-md hover:shadow-lg text-center group/item"
                                    >
                                        <i class="fas fa-calendar-check mr-1.5 sm:mr-2 group-hover/item:translate-x-1 transition-transform inline-block text-xs sm:text-sm"></i>
                                        <span class="hidden sm:inline">Book Now</span>
                                        <span class="sm:hidden">Book</span>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl shadow-lg px-4">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                                <i class="fas fa-tools text-gray-400 text-3xl sm:text-4xl"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">No Services Available</h3>
                            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">We're currently setting up our service categories. Please check back soon!</p>
                            <a href="{{ route('contact') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-sm sm:text-base">
                                <i class="fas fa-envelope mr-2"></i>
                                Contact Us
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- No Results Message -->
            <div x-show="(searchQuery || selectedCategory) && filteredCategories.length === 0 && !loading" 
                 x-transition
                 class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl shadow-lg px-4 mx-4 sm:mx-0">
                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-search text-gray-400 text-3xl sm:text-4xl"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">No Services Found</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Try adjusting your search or filter criteria</p>
                <button 
                    @click="clearSearch()"
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base font-semibold transition-colors"
                >
                    <i class="fas fa-refresh mr-2"></i>
                    Clear Search
                </button>
            </div>
        </div>
    </section>

    <!-- Contact Us CTA Section -->
    <section class="py-12 sm:py-16 md:py-20 bg-gray-800 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-gray-700 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-12 border border-gray-600 shadow-xl">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-600 rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6">
                    <i class="fas fa-headset text-white text-2xl sm:text-3xl"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 px-2">Don't See What You're Looking For?</h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-300 mb-6 sm:mb-8 max-w-2xl mx-auto leading-relaxed px-2">
                    Can't find the service you need? Contact our support team and we'll help you find the right professional for your specific needs.
                </p>
                <a 
                    href="{{ route('contact') }}" 
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg sm:rounded-xl text-base sm:text-lg font-bold transition-all shadow-md hover:shadow-lg transform hover:scale-105"
                >
                    <i class="fas fa-envelope mr-2 sm:mr-3"></i>
                    Contact Us
                    <i class="fas fa-arrow-right ml-2 sm:ml-3"></i>
                </a>
            </div>
        </div>
    </section>
</div>

@php
    $categoriesData = $categories->map(function($cat) {
        return [
            'id' => $cat->id,
            'name' => $cat->name,
            'description' => $cat->description,
            'servicemen_count' => $cat->servicemen_count ?? 0,
            'icon' => $cat->icon ?? 'tools',
        ];
    })->values()->all();
@endphp

@push('scripts')
<script>
function servicesPage() {
    return {
        searchQuery: '',
        selectedCategory: '',
        loading: false,
        categories: @json($categoriesData),
        
        get totalCategories() {
            return this.categories.length;
        },
        
        get totalServicemen() {
            return this.categories.reduce((sum, cat) => sum + (cat.servicemen_count || 0), 0);
        },
        
        get filteredCategories() {
            let filtered = [...this.categories];
            
            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(cat => 
                    cat.name.toLowerCase().includes(query) ||
                    (cat.description && cat.description.toLowerCase().includes(query))
                );
            }
            
            // Apply category dropdown filter
            if (this.selectedCategory) {
                filtered = filtered.filter(cat => cat.id == this.selectedCategory);
            }
            
            return filtered;
        },
        
        filterServices() {
            // Reactive filtering happens automatically through computed property
            // This method is called on input/change for side effects
        },
        
        formatCount(count) {
            // Ensure count is a number and format it properly
            const num = parseInt(count) || 0;
            return num.toString();
        },
        
        isVisible(categoryId) {
            return this.filteredCategories.some(cat => cat.id === categoryId);
        },
        
        clearSearch() {
            this.searchQuery = '';
            this.selectedCategory = '';
        }
    }
}
</script>
@endpush
@endsection

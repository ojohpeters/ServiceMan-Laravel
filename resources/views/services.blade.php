@extends('layouts.app')

@section('title', 'Services - ServiceMan')
@section('description', 'Browse our comprehensive range of professional services including electrical, plumbing, HVAC, and more.')

@section('content')
<div x-data="servicesPage()" x-init="loadCategories()" class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-bold mb-6">Professional Services</h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                    Choose from our comprehensive range of professional services. 
                    All our servicemen are verified, skilled, and ready to help.
                </p>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="py-8 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Search Bar -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            @input="filterCategories()"
                            placeholder="Search services..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-2">
                    <button 
                        @click="filterBy = 'all'; filterCategories()"
                        :class="filterBy === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        All Services
                    </button>
                    <button 
                        @click="filterBy = 'popular'; filterCategories()"
                        :class="filterBy === 'popular' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Popular
                    </button>
                    <button 
                        @click="filterBy = 'emergency'; filterCategories()"
                        :class="filterBy === 'emergency' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Emergency
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Loading State -->
            <div x-show="loading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Loading services...</p>
            </div>

            <!-- Services Grid -->
            <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="category in filteredCategories" :key="category.id">
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Category Image -->
                        <div class="h-48 bg-gradient-to-br from-blue-500 to-purple-600 rounded-t-2xl flex items-center justify-center">
                            <i :class="getCategoryIcon(category.name)" class="text-white text-6xl"></i>
                        </div>

                        <!-- Category Content -->
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2" x-text="category.name"></h3>
                            <p class="text-gray-600 mb-4" x-text="category.description || 'Professional ' + category.name.toLowerCase() + ' services'"></p>
                            
                            <!-- Stats -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-user-friends mr-2"></i>
                                    <span x-text="category.servicemen_count || '0'">0</span> professionals
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-star mr-2 text-yellow-500"></i>
                                    <span x-text="category.average_rating || '4.8'">4.8</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button 
                                    @click="viewCategory(category)"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                                >
                                    <i class="fas fa-eye mr-2"></i>
                                    View Details
                                </button>
                                <button 
                                    @click="bookService(category)"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                                >
                                    <i class="fas fa-calendar-plus mr-2"></i>
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No Results -->
            <div x-show="!loading && filteredCategories.length === 0" class="text-center py-12">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No services found</h3>
                <p class="text-gray-600">Try adjusting your search or filter criteria</p>
            </div>
        </div>
    </section>

    <!-- Popular Services Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Most Popular Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    These are the most requested services on our platform. 
                    Book with confidence knowing you're getting the best professionals.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Popular Service 1 -->
                <div class="bg-gray-50 rounded-xl p-6 text-center hover:bg-gray-100 transition-colors cursor-pointer" @click="bookService({name: 'Electrical Services', id: 1})">
                    <div class="w-16 h-16 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bolt text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Electrical Services</h3>
                    <p class="text-sm text-gray-600 mb-3">Starting from ₦5,000</p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        <span>4.9 (127 reviews)</span>
                    </div>
                </div>

                <!-- Popular Service 2 -->
                <div class="bg-gray-50 rounded-xl p-6 text-center hover:bg-gray-100 transition-colors cursor-pointer" @click="bookService({name: 'Plumbing', id: 2})">
                    <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-wrench text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Plumbing</h3>
                    <p class="text-sm text-gray-600 mb-3">Starting from ₦3,000</p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        <span>4.8 (98 reviews)</span>
                    </div>
                </div>

                <!-- Popular Service 3 -->
                <div class="bg-gray-50 rounded-xl p-6 text-center hover:bg-gray-100 transition-colors cursor-pointer" @click="bookService({name: 'HVAC Services', id: 3})">
                    <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-thermometer-half text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">HVAC Services</h3>
                    <p class="text-sm text-gray-600 mb-3">Starting from ₦8,000</p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        <span>4.7 (76 reviews)</span>
                    </div>
                </div>

                <!-- Popular Service 4 -->
                <div class="bg-gray-50 rounded-xl p-6 text-center hover:bg-gray-100 transition-colors cursor-pointer" @click="bookService({name: 'Carpentry', id: 4})">
                    <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hammer text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Carpentry</h3>
                    <p class="text-sm text-gray-600 mb-3">Starting from ₦4,000</p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                        <span>4.6 (54 reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Services Banner -->
    <section class="py-12 bg-red-50 border-l-4 border-red-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Emergency Services Available</h3>
                        <p class="text-gray-600">Need immediate assistance? Our emergency services are available 24/7.</p>
                    </div>
                </div>
                <button 
                    @click="bookEmergencyService()"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                >
                    <i class="fas fa-phone mr-2"></i>
                    Call Emergency
                </button>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function servicesPage() {
    return {
        categories: [],
        filteredCategories: [],
        loading: true,
        searchQuery: '',
        filterBy: 'all',

        async loadCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                
                if (response.ok) {
                    this.categories = data.data || [];
                    this.filteredCategories = [...this.categories];
                } else {
                    console.error('Failed to load categories:', data.message);
                    // Fallback to demo data
                    this.loadDemoCategories();
                }
            } catch (error) {
                console.error('Error loading categories:', error);
                // Fallback to demo data
                this.loadDemoCategories();
            } finally {
                this.loading = false;
            }
        },

        loadDemoCategories() {
            this.categories = [
                {
                    id: 1,
                    name: 'Electrical Services',
                    description: 'Professional electrical repairs, installations, and maintenance',
                    servicemen_count: 45,
                    average_rating: 4.9
                },
                {
                    id: 2,
                    name: 'Plumbing',
                    description: 'Expert plumbing services for all your water and drainage needs',
                    servicemen_count: 38,
                    average_rating: 4.8
                },
                {
                    id: 3,
                    name: 'HVAC Services',
                    description: 'Heating, ventilation, and air conditioning solutions',
                    servicemen_count: 28,
                    average_rating: 4.7
                },
                {
                    id: 4,
                    name: 'Carpentry',
                    description: 'Custom woodwork and furniture repairs and installations',
                    servicemen_count: 32,
                    average_rating: 4.6
                },
                {
                    id: 5,
                    name: 'Painting',
                    description: 'Interior and exterior painting services',
                    servicemen_count: 25,
                    average_rating: 4.5
                },
                {
                    id: 6,
                    name: 'Cleaning Services',
                    description: 'Professional cleaning for homes and offices',
                    servicemen_count: 42,
                    average_rating: 4.8
                }
            ];
            this.filteredCategories = [...this.categories];
        },

        filterCategories() {
            let filtered = [...this.categories];

            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(category => 
                    category.name.toLowerCase().includes(query) ||
                    (category.description && category.description.toLowerCase().includes(query))
                );
            }

            // Apply category filter
            if (this.filterBy === 'popular') {
                filtered = filtered.filter(category => category.average_rating >= 4.7);
            } else if (this.filterBy === 'emergency') {
                // For demo purposes, mark some categories as emergency
                const emergencyCategories = ['Electrical Services', 'Plumbing', 'HVAC Services'];
                filtered = filtered.filter(category => emergencyCategories.includes(category.name));
            }

            this.filteredCategories = filtered;
        },

        getCategoryIcon(categoryName) {
            const iconMap = {
                'Electrical Services': 'fas fa-bolt',
                'Plumbing': 'fas fa-wrench',
                'HVAC Services': 'fas fa-thermometer-half',
                'Carpentry': 'fas fa-hammer',
                'Painting': 'fas fa-paint-brush',
                'Cleaning Services': 'fas fa-broom'
            };
            return iconMap[categoryName] || 'fas fa-tools';
        },

        viewCategory(category) {
            // For now, just show an alert. In a real app, this would navigate to category details
            alert(`Viewing details for ${category.name}`);
        },

        bookService(category) {
            // Check if user is authenticated
            const token = localStorage.getItem('auth_token');
            if (!token) {
                // Redirect to login with return URL
                window.location.href = `/login?redirect=${encodeURIComponent('/dashboard')}`;
                return;
            }

            // For now, just show an alert. In a real app, this would open booking modal
            alert(`Booking ${category.name} service...`);
        },

        bookEmergencyService() {
            // Check if user is authenticated
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = `/login?redirect=${encodeURIComponent('/dashboard')}`;
                return;
            }

            alert('Emergency service booking...');
        }
    }
}
</script>
@endpush
@endsection


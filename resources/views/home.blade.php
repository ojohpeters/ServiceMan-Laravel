@extends('layouts.app')

@section('title', 'ServiceMan - Professional On-Demand Services')
@section('description', 'Connect with skilled professionals for electrical, plumbing, HVAC, and more. Book services instantly with our trusted platform.')

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
    
    .animate-slide-up {
        animation: slideInUp 0.6s ease-out;
    }
    
    .service-card {
        transition: all 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    
    .feature-card:hover {
        transform: scale(1.05);
    }
</style>
@endpush

@section('content')
<div x-data="landingPage()" x-init="init()">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white min-h-screen flex items-center overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 50px 50px;"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-center lg:text-left">
                    <div class="inline-block mb-6 px-4 py-2 bg-yellow-500 text-gray-900 rounded-full font-bold text-sm">
                        <i class="fas fa-bolt mr-2"></i>
                        Trusted by {{ number_format($stats['total_clients']) }}+ Clients
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 leading-tight text-white">
                        Professional
                        <span class="block text-yellow-400">Services</span>
                        <span class="block">At Your Doorstep</span>
                    </h1>
                    
                    <p class="text-xl lg:text-2xl mb-10 text-white leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Connect with verified professionals for electrical, plumbing, HVAC, and more. 
                        Book services instantly with our secure platform.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                        <a href="{{ url('/register') }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-8 py-4 rounded-lg text-lg font-bold transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-rocket mr-2"></i>
                            Get Started Free
                        </a>
                        <a href="{{ url('/services') }}" 
                           class="border-2 border-white text-white hover:bg-white hover:text-blue-700 px-8 py-4 rounded-lg text-lg font-bold transition-all duration-300">
                            <i class="fas fa-search mr-2"></i>
                            Browse Services
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div class="bg-gray-900 bg-opacity-60 backdrop-blur-md rounded-lg p-4 border-2 border-yellow-400 border-opacity-50 shadow-lg">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" x-data="{ count: 0, target: {{ $stats['total_clients'] }} }" x-init="setTimeout(() => { let duration = 2000; let step = target / (duration / 16); let timer = setInterval(() => { count += step; if (count >= target) { count = target; clearInterval(timer); } }, 16); }, 500)"><span x-text="Math.floor(count)"></span>+</div>
                            <div class="text-white text-sm font-bold">Happy Clients</div>
                        </div>
                        <div class="bg-gray-900 bg-opacity-60 backdrop-blur-md rounded-lg p-4 border-2 border-yellow-400 border-opacity-50 shadow-lg">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" x-data="{ count: 0, target: {{ $stats['total_servicemen'] }} }" x-init="setTimeout(() => { let duration = 2000; let step = target / (duration / 16); let timer = setInterval(() => { count += step; if (count >= target) { count = target; clearInterval(timer); } }, 16); }, 700)"><span x-text="Math.floor(count)"></span>+</div>
                            <div class="text-white text-sm font-bold">Experts</div>
                        </div>
                        <div class="bg-gray-900 bg-opacity-60 backdrop-blur-md rounded-lg p-4 border-2 border-yellow-400 border-opacity-50 shadow-lg">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1">{{ number_format($stats['total_services_completed']) }}+</div>
                            <div class="text-white text-sm font-bold">Services Completed</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Service Cards -->
                <div class="grid grid-cols-2 gap-4">
                    @php
                        // Map category names to FontAwesome icons
                        $iconMap = [
                            'Electrical' => 'bolt',
                            'Plumbing' => 'wrench',
                            'HVAC' => 'thermometer-half',
                            'Carpentry' => 'hammer',
                            'Painting' => 'paint-brush',
                            'Cleaning' => 'broom',
                            'Appliance Repair' => 'toolbox',
                            'Gardening' => 'seedling',
                        ];
                        
                        // Use categories if available, otherwise use fallback
                        if ($categories->count() > 0) {
                            $displayCategories = $categories->take(3);
                        } else {
                            $displayCategories = collect([
                                (object)['name' => 'Electrical Services', 'description' => 'Professional electrical repairs and installations'],
                                (object)['name' => 'Plumbing', 'description' => 'Expert plumbing solutions'],
                                (object)['name' => 'HVAC', 'description' => 'Climate control experts'],
                            ]);
                        }
                    @endphp
                    
                    @foreach($displayCategories as $index => $category)
                        @php
                            $isFirst = $index === 0;
                            $cardClasses = $isFirst ? 'col-span-2 lg:col-span-1' : '';
                            $iconSize = $isFirst ? 'w-14 h-14' : 'w-12 h-12';
                            $iconClass = $isFirst ? 'text-xl' : '';
                            $titleClass = $isFirst ? 'text-lg' : 'text-base';
                            $textClass = $isFirst ? 'text-sm' : 'text-xs';
                            $alignClass = $isFirst ? 'text-center lg:text-left' : 'text-center';
                            
                            // Get icon from category name
                            $categoryName = $category->name;
                            $iconKey = explode(' ', $categoryName)[0]; // Get first word (e.g., "Electrical" from "Electrical Services")
                            $icon = $iconMap[$iconKey] ?? 'tools';
                        @endphp
                        <div class="bg-gray-900 bg-opacity-70 backdrop-blur-md rounded-xl p-6 border-2 border-yellow-400 border-opacity-50 {{ $cardClasses }} shadow-xl hover:border-yellow-400 hover:scale-105 transition-all">
                            <div class="{{ $iconSize }} bg-yellow-500 rounded-xl flex items-center justify-center mb-{{ $isFirst ? '4' : '3' }} mx-auto {{ $isFirst ? 'lg:mx-0' : '' }} shadow-md transform hover:scale-110 transition-transform">
                                <i class="fas fa-{{ $icon }} text-gray-900 {{ $iconClass }}"></i>
                            </div>
                            <h3 class="{{ $titleClass }} font-bold mb-{{ $isFirst ? '2' : '1' }} {{ $alignClass }} text-white">{{ $categoryName }}</h3>
                            <p class="text-gray-200 {{ $textClass }} {{ $alignClass }} font-medium">{{ Str::limit($category->description ?? 'Professional service', 40) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
            <a href="#how-it-works" class="text-yellow-400 text-3xl hover:text-yellow-300 transition-colors animate-bounce">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get professional services in three simple steps
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="feature-card text-center">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                            <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-check text-white text-3xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-lg shadow-lg">
                            1
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Book a Service</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Describe your service needs and schedule a convenient time. 
                        Our system matches you with qualified professionals instantly.
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="feature-card text-center">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto">
                            <div class="w-20 h-20 bg-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-check text-white text-3xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-lg shadow-lg">
                            2
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Get Matched</h3>
                    <p class="text-gray-600 leading-relaxed">
                        We connect you with verified professionals who have the skills 
                        and experience for your specific requirements.
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="feature-card text-center">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                            <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-3xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-lg shadow-lg">
                            3
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Enjoy Quality Service</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sit back and relax while professionals deliver high-quality service. 
                        Pay securely and rate your experience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Our Services</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    From electrical repairs to plumbing emergencies, we cover all your service needs.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($categories as $category)
                    <div class="service-card bg-white rounded-xl shadow-lg p-8 border border-gray-200">
                        <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-{{ $category->icon ?? 'tools' }} text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $category->name }}</h3>
                        <p class="text-gray-600 mb-6">{{ $category->description ?? 'Professional service in this category' }}</p>
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                            <span class="text-sm text-gray-600 font-semibold">
                                <i class="fas fa-users mr-2 text-blue-600"></i>{{ $category->servicemen_count ?? 0 }} Professionals
                            </span>
                        </div>
                        <a href="{{ route('services.category', $category) }}" class="text-blue-600 hover:text-blue-700 font-bold transition-colors inline-flex items-center">
                            View Services <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('services') }}" 
                   class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-bold transition-colors shadow-lg">
                    View All Services <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-8">Why Choose ServiceMan?</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Verified Professionals</h3>
                                <p class="text-gray-600">All our servicemen are background-checked and verified for your safety and peace of mind.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">24/7 Availability</h3>
                                <p class="text-gray-600">Book services anytime, anywhere. Emergency services available round the clock.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Transparent Pricing</h3>
                                <p class="text-gray-600">No hidden fees. Get upfront pricing before booking. Fair rates for quality work.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Quality Guaranteed</h3>
                                <p class="text-gray-600">100% satisfaction guarantee. We stand behind our work and our professionals.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl overflow-hidden shadow-2xl">
                        <div class="aspect-video flex items-center justify-center">
                            <div class="text-center text-white">
                                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:bg-opacity-30 transition-all">
                                    <i class="fas fa-play text-3xl ml-1"></i>
                                </div>
                                <p class="text-lg font-bold">Watch How It Works</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Servicemen Section -->
    @if($featuredServicemen->count() > 0)
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Featured Professionals</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Meet some of our top-rated service professionals
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredServicemen as $serviceman)
                    <div class="service-card bg-white rounded-xl shadow-lg p-6 border border-gray-200 text-center">
                        <img src="{{ $serviceman->profile_picture_url }}" 
                             alt="{{ $serviceman->full_name }}" 
                             class="w-20 h-20 rounded-full object-cover mx-auto mb-4 border-4 border-blue-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $serviceman->full_name }}</h3>
                        <p class="text-sm text-gray-600 mb-3">{{ $serviceman->servicemanProfile->category->name ?? 'Professional' }}</p>
                        
                        @php
                            // Use profile rating which is already calculated, fallback to avg from ratings
                            $profileRating = $serviceman->servicemanProfile->rating ?? 0;
                            if ($profileRating == 0 && $serviceman->ratingsReceived->count() > 0) {
                                $profileRating = $serviceman->ratingsReceived->avg('rating');
                            }
                            $displayRating = number_format($profileRating, 1);
                            
                            // Get real job count from servicemanProfile
                            $jobCount = $serviceman->servicemanProfile->total_jobs_completed ?? 0;
                        @endphp
                        
                        @if($profileRating > 0)
                            <div class="flex items-center justify-center mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $profileRating ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600 font-semibold">({{ $displayRating }})</span>
                            </div>
                        @endif
                        
                        <div class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-briefcase mr-1 text-blue-600"></i>
                            {{ number_format($jobCount) }} {{ $jobCount == 1 ? 'Job' : 'Jobs' }}
                        </div>
                        
                        <a href="{{ route('servicemen.show', $serviceman) }}" 
                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-bold transition-colors">
                            View Profile
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Testimonials Section -->
    @if($testimonials->count() > 0)
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">What Our Clients Say</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Don't just take our word for it. Here's what our satisfied clients have to say.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($testimonials as $testimonial)
                    @php
                        $client = $testimonial->client;
                        $initials = strtoupper(substr($client->first_name ?? '', 0, 1) . substr($client->last_name ?? '', 0, 1));
                        $location = $testimonial->serviceRequest->location ?? 'Nigeria';
                        $colorClasses = ['bg-blue-600', 'bg-purple-600', 'bg-green-600', 'bg-yellow-600', 'bg-pink-600', 'bg-indigo-600'];
                        $colorClass = $colorClasses[$loop->index % count($colorClasses)];
                    @endphp
                    <div class="bg-gray-50 rounded-xl p-8 border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $testimonial->rating ? '' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 italic leading-relaxed">
                            "{{ Str::limit($testimonial->review, 200) }}"
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 {{ $colorClass }} rounded-full flex items-center justify-center text-white font-bold mr-4">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $client->full_name }}</div>
                                <div class="text-gray-600 text-sm">
                                    {{ $testimonial->serviceRequest->category->name ?? 'Service' }} • {{ $location }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl lg:text-5xl font-bold mb-6 text-white">Ready to Get Started?</h2>
            <p class="text-xl mb-10 text-white max-w-2xl mx-auto">
                Join {{ number_format($stats['total_clients']) }}+ satisfied clients who trust ServiceMan for their service needs. 
                Book your first service today!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/register') }}" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-8 py-4 rounded-lg text-lg font-bold transition-colors shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </a>
                <a href="{{ url('/services') }}" 
                   class="border-2 border-white text-white hover:bg-white hover:text-blue-700 px-8 py-4 rounded-lg text-lg font-bold transition-all">
                    <i class="fas fa-search mr-2"></i>
                    Browse Services
                </a>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function landingPage() {
    return {
        init() {
            // Intersection Observer for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-slide-up');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.service-card, .feature-card').forEach(el => {
                observer.observe(el);
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }
    }
}
</script>
@endpush
@endsection

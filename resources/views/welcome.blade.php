@extends('layouts.app')

@section('title', 'ServiceMan - Professional On-Demand Services')
@section('description', 'Connect with skilled professionals for electrical, plumbing, HVAC, and more. Book services instantly with our trusted platform.')

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
        50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    
    .animate-pulse-glow {
        animation: pulse-glow 3s ease-in-out infinite;
    }
    
    .animate-slide-up {
        animation: slideInUp 0.8s ease-out;
    }
    
    .animate-slide-left {
        animation: slideInLeft 0.8s ease-out;
    }
    
    .animate-slide-right {
        animation: slideInRight 0.8s ease-out;
    }
    
    .animate-scale-in {
        animation: scaleIn 0.6s ease-out;
    }
    
    .service-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    
    .service-card:hover {
        transform: translateY(-10px) rotateX(5deg) rotateY(5deg);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .stat-card {
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .stat-card:hover {
        transform: scale(1.05);
        background: rgba(255, 255, 255, 0.2);
    }
    
    .hero-bg {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #2563eb 100%);
        position: relative;
        overflow: hidden;
    }
    
    .hero-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        animation: pulse 4s ease-in-out infinite;
    }
    
    .particle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 15s infinite linear;
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .feature-icon {
        transition: all 0.4s ease;
    }
    
    .feature-card:hover .feature-icon {
        transform: rotateY(360deg) scale(1.1);
    }
    
    .testimonial-card {
        transition: all 0.3s ease;
    }
    
    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    
    @media (max-width: 768px) {
        .service-card:hover {
            transform: translateY(-5px);
        }
    }
</style>
@endpush

@section('content')
<div x-data="landingPage()" x-init="init()">
    <!-- Hero Section with 3D Effects -->
    <section class="relative hero-bg text-white min-h-screen flex items-center overflow-hidden">
        <!-- Animated Particles -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="particle" style="width: 80px; height: 80px; left: 10%; top: 20%; animation-delay: 0s;"></div>
            <div class="particle" style="width: 120px; height: 120px; left: 70%; top: 60%; animation-delay: 2s;"></div>
            <div class="particle" style="width: 60px; height: 60px; left: 40%; top: 80%; animation-delay: 4s;"></div>
            <div class="particle" style="width: 100px; height: 100px; left: 80%; top: 30%; animation-delay: 6s;"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-center lg:text-left animate-slide-left">
                    <div class="inline-block mb-6 px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-full border border-white border-opacity-30">
                        <span class="text-sm font-semibold flex items-center">
                            <i class="fas fa-bolt text-yellow-400 mr-2"></i>
                            Trusted by 10,000+ Clients
                        </span>
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                        Professional
                        <span class="gradient-text block">Services</span>
                        <span class="block">At Your Doorstep</span>
                    </h1>
                    
                    <p class="text-xl lg:text-2xl mb-8 text-blue-100 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Connect with verified professionals for electrical, plumbing, HVAC, and more. 
                        Book services instantly with our secure platform.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                        <a href="{{ url('/register') }}" 
                           class="group relative bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl overflow-hidden">
                            <span class="relative z-10 flex items-center justify-center">
                                <i class="fas fa-rocket mr-2"></i>
                                Get Started Free
                            </span>
                            <span class="absolute inset-0 bg-yellow-400 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></span>
                        </a>
                        <a href="{{ url('/services') }}" 
                           class="group border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 backdrop-blur-sm bg-white bg-opacity-10">
                            <i class="fas fa-search mr-2"></i>
                            Browse Services
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 text-center lg:text-left">
                        <div class="stat-card bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" x-data="{ count: 0 }" x-init="setTimeout(() => { let target = 1000; let duration = 2000; let step = target / (duration / 16); let timer = setInterval(() => { count += step; if (count >= target) { count = target; clearInterval(timer); } }, 16); }, 500)"><span x-text="Math.floor(count)"></span>+</div>
                            <div class="text-blue-200 text-sm lg:text-base">Happy Clients</div>
                        </div>
                        <div class="stat-card bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1" x-data="{ count: 0 }" x-init="setTimeout(() => { let target = 500; let duration = 2000; let step = target / (duration / 16); let timer = setInterval(() => { count += step; if (count >= target) { count = target; clearInterval(timer); } }, 16); }, 700)"><span x-text="Math.floor(count)"></span>+</div>
                            <div class="text-blue-200 text-sm lg:text-base">Experts</div>
                        </div>
                        <div class="stat-card bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-4 border border-white border-opacity-20">
                            <div class="text-3xl lg:text-4xl font-bold text-yellow-400 mb-1">24/7</div>
                            <div class="text-blue-200 text-sm lg:text-base">Support</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - 3D Service Cards -->
                <div class="grid grid-cols-2 gap-4 lg:gap-6 animate-slide-right">
                    <div class="service-card bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 border border-white border-opacity-20 col-span-2 lg:col-span-1">
                        <div class="w-16 h-16 bg-yellow-500 rounded-2xl flex items-center justify-center mb-4 mx-auto lg:mx-0 transform rotate-3 hover:rotate-6 transition-transform">
                            <i class="fas fa-bolt text-gray-900 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-2 text-center lg:text-left">Electrical Services</h3>
                        <p class="text-blue-100 text-sm text-center lg:text-left">Professional electrical repairs and installations</p>
                    </div>
                    
                    <div class="service-card bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 border border-white border-opacity-20">
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mb-4 mx-auto transform -rotate-3 hover:rotate-0 transition-transform">
                            <i class="fas fa-wrench text-gray-900 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-center">Plumbing</h3>
                        <p class="text-blue-100 text-xs text-center">Expert plumbing solutions</p>
                    </div>
                    
                    <div class="service-card bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 border border-white border-opacity-20">
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mb-4 mx-auto transform rotate-3 hover:-rotate-3 transition-transform">
                            <i class="fas fa-thermometer-half text-gray-900 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-center">HVAC</h3>
                        <p class="text-blue-100 text-xs text-center">Climate control experts</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#how-it-works" class="text-white text-4xl">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50 to-transparent opacity-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-slide-up">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get professional services in three simple steps. Our streamlined process ensures 
                    you get the best service professionals for your needs.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                <!-- Step 1 -->
                <div class="feature-card text-center animate-scale-in" style="animation-delay: 0.1s">
                    <div class="relative mb-8">
                        <div class="w-32 h-32 bg-blue-100 rounded-full flex items-center justify-center mx-auto feature-icon">
                            <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-calendar-check text-white text-4xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-xl shadow-lg">
                            1
                        </div>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Book a Service</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Describe your service needs and schedule a convenient time. 
                        Our system matches you with qualified professionals instantly.
                    </p>
                </div>
                
                <!-- Step 2 -->
                <div class="feature-card text-center animate-scale-in" style="animation-delay: 0.3s">
                    <div class="relative mb-8">
                        <div class="w-32 h-32 bg-purple-100 rounded-full flex items-center justify-center mx-auto feature-icon">
                            <div class="w-24 h-24 bg-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-user-check text-white text-4xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-xl shadow-lg">
                            2
                        </div>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Get Matched</h3>
                    <p class="text-gray-600 leading-relaxed">
                        We connect you with verified professionals who have the skills 
                        and experience for your specific requirements.
                    </p>
                </div>
                
                <!-- Step 3 -->
                <div class="feature-card text-center animate-scale-in" style="animation-delay: 0.5s">
                    <div class="relative mb-8">
                        <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center mx-auto feature-icon">
                            <div class="w-24 h-24 bg-green-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-circle text-white text-4xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-gray-900 font-bold text-xl shadow-lg">
                            3
                        </div>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Enjoy Quality Service</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sit back and relax while professionals deliver high-quality service. 
                        Pay securely and rate your experience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview Section -->
    <section class="py-20 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Our Services</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    From electrical repairs to plumbing emergencies, we cover all your home and office service needs.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Electrical Services -->
                <div class="service-card bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl border border-gray-100">
                    <div class="w-20 h-20 bg-yellow-100 rounded-2xl flex items-center justify-center mb-6 transform rotate-3 hover:rotate-6 transition-transform">
                        <i class="fas fa-bolt text-yellow-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Electrical Services</h3>
                    <ul class="text-gray-600 space-y-3 mb-6">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Wiring & Installation
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Electrical Repairs
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Power Outage Fixes
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Safety Inspections
                        </li>
                    </ul>
                    <a href="{{ url('/services') }}" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors inline-flex items-center group">
                        Learn More <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- Plumbing Services -->
                <div class="service-card bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl border border-gray-100">
                    <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 transform -rotate-3 hover:rotate-3 transition-transform">
                        <i class="fas fa-wrench text-blue-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">Plumbing Services</h3>
                    <ul class="text-gray-600 space-y-3 mb-6">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Pipe Repairs
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Drain Cleaning
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Faucet Installation
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Water Heater Service
                        </li>
                    </ul>
                    <a href="{{ url('/services') }}" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors inline-flex items-center group">
                        Learn More <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                
                <!-- HVAC Services -->
                <div class="service-card bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl border border-gray-100">
                    <div class="w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 transform rotate-3 hover:-rotate-3 transition-transform">
                        <i class="fas fa-thermometer-half text-purple-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">HVAC Services</h3>
                    <ul class="text-gray-600 space-y-3 mb-6">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            AC Installation
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Heating Repair
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Duct Cleaning
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            Maintenance
                        </li>
                    </ul>
                    <a href="{{ url('/services') }}" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors inline-flex items-center group">
                        Learn More <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ url('/services') }}" 
                   class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg group">
                    View All Services <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Video/Image Showcase Section -->
    <section class="py-20 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="relative group">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl transform group-hover:scale-105 transition-transform duration-500">
                        <!-- Video Placeholder - Replace with actual video or image -->
                        <div class="aspect-video bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                            <div class="text-center text-white">
                                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:bg-opacity-30 transition-all animate-pulse-glow">
                                    <i class="fas fa-play text-3xl ml-1"></i>
                                </div>
                                <p class="text-lg font-semibold">Watch How It Works</p>
                            </div>
                        </div>
                        <!-- You can replace the above div with: <video class="w-full h-full object-cover" autoplay muted loop><source src="/videos/demo.mp4" type="video/mp4"></video> -->
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-yellow-500 rounded-full opacity-20 blur-3xl group-hover:opacity-30 transition-opacity"></div>
                </div>
                
                <div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">Why Choose ServiceMan?</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Verified Professionals</h3>
                                <p class="text-gray-600">All our servicemen are background-checked and verified for your safety and peace of mind.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">24/7 Availability</h3>
                                <p class="text-gray-600">Book services anytime, anywhere. Emergency services available round the clock.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Transparent Pricing</h3>
                                <p class="text-gray-600">No hidden fees. Get upfront pricing before booking. Fair rates for quality work.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Quality Guaranteed</h3>
                                <p class="text-gray-600">100% satisfaction guarantee. We stand behind our work and our professionals.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">What Our Clients Say</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Don't just take our word for it. Here's what our satisfied clients have to say.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 text-lg">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 italic leading-relaxed">
                        "Excellent service! The electrician was professional, on time, and fixed the issue quickly. 
                        Highly recommended!"
                    </p>
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-4 shadow-lg">
                            AS
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Adebayo Samuel</div>
                            <div class="text-gray-500 text-sm">Lagos, Nigeria</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 text-lg">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 italic leading-relaxed">
                        "Great platform! Found a reliable plumber within minutes. The service was top-notch and reasonably priced."
                    </p>
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-4 shadow-lg">
                            FO
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Fatima Okafor</div>
                            <div class="text-gray-500 text-sm">Abuja, Nigeria</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 text-lg">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 italic leading-relaxed">
                        "Professional HVAC service that exceeded my expectations. Will definitely use ServiceMan again!"
                    </p>
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-4 shadow-lg">
                            MJ
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Michael Johnson</div>
                            <div class="text-gray-500 text-sm">Port Harcourt, Nigeria</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 text-white relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-yellow-500 rounded-full filter blur-3xl opacity-20 transform -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full filter blur-3xl opacity-20 transform translate-x-1/2 translate-y-1/2"></div>
        </div>
        
        <div class="relative max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl lg:text-5xl font-bold mb-6">Ready to Get Started?</h2>
            <p class="text-xl mb-10 text-blue-100 max-w-2xl mx-auto">
                Join thousands of satisfied clients who trust ServiceMan for their service needs. 
                Book your first service today and experience the difference!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/register') }}" 
                   class="group bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </a>
                <a href="{{ url('/services') }}" 
                   class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 backdrop-blur-sm bg-white bg-opacity-10">
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
            document.querySelectorAll('.service-card, .testimonial-card, .feature-card').forEach(el => {
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

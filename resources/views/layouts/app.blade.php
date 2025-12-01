<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ServiceMan') }} - @yield('title', 'On-Demand Service Platform')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    
    <!-- Meta tags -->
    <meta name="description" content="@yield('description', 'Professional on-demand service platform connecting clients with skilled servicemen for electrical, plumbing, HVAC and more.')">
    <meta name="keywords" content="services, repair, maintenance, electrical, plumbing, HVAC, booking, on-demand">
    
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation -->
    <nav class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                            <i class="fas fa-tools text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">ServiceMan</span>
                            <p class="text-xs text-gray-500 hidden sm:block">Professional Services</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    @guest
                        <a href="{{ url('/') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('/') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-home mr-1"></i>Home
                        </a>
                        <a href="{{ url('/services') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('services*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-concierge-bell mr-1"></i>Services
                        </a>
                        <a href="{{ url('/about') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('about') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-info-circle mr-1"></i>About
                        </a>
                        <a href="{{ url('/contact') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('contact') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-envelope mr-1"></i>Contact
                        </a>
                        
                        <!-- Auth Links -->
                        <div class="flex items-center space-x-3 ml-4">
                            <a href="{{ url('/login') }}" class="text-gray-700 hover:text-blue-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-sign-in-alt mr-1"></i>Login
                            </a>
                            <a href="{{ url('/register') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-user-plus mr-1"></i>Get Started
                            </a>
                        </div>
                    @else
                        <!-- Logged In Navigation - Clean & Role-Specific -->
                        <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('dashboard*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        
                        @if(Auth::user()->isClient())
                            <a href="{{ url('/services') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('services*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-concierge-bell mr-1"></i>Browse Services
                            </a>
                            <a href="{{ url('/service-requests') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('service-requests*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-clipboard-list mr-1"></i>My Bookings
                            </a>
                        @elseif(Auth::user()->isServiceman())
                            <a href="{{ url('/service-requests') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('service-requests*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-briefcase mr-1"></i>My Jobs
                            </a>
                            <a href="{{ route('profile.serviceman') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('profile/serviceman') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-user-cog mr-1"></i>My Profile
                            </a>
                        @elseif(Auth::user()->isAdmin())
                            <a href="{{ route('admin.service-requests') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('admin/service-requests*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-tasks mr-1"></i>Requests
                            </a>
                            <a href="{{ route('admin.categories') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('admin/categories*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-tags mr-1"></i>Categories
                            </a>
                            <a href="{{ route('admin.users') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('admin/users*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-users mr-1"></i>Users
                            </a>
                            <a href="{{ route('admin.testimonials') }}" class="text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ request()->is('admin/testimonials*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                <i class="fas fa-star mr-1"></i>Testimonials
                            </a>
                        @endif
                        
                        <!-- Notifications -->
                        <div class="ml-3">
                        <x-notifications />
                        </div>
                        
                        <!-- User Menu - Simplified -->
                        <div class="relative ml-3" x-data="{ open: false }" @keydown.escape="open = false">
                            <button @click="open = !open" type="button" class="flex items-center space-x-2 hover:bg-gray-100 px-2 py-1.5 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <img src="{{ Auth::user()->profile_picture_url }}" 
                                     alt="{{ Auth::user()->full_name }}" 
                                     class="w-8 h-8 rounded-full object-cover border-2 border-blue-200 shadow-sm">
                                <i class="fas fa-chevron-down text-xs text-gray-400 hidden lg:inline transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 @click="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl py-2 z-50 border border-gray-200"
                                 style="display: none;">
                                <!-- User Info -->
                                <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200">
                                    <p class="text-sm font-bold text-gray-900">{{ Auth::user()->full_name }}</p>
                                    <p class="text-xs text-gray-600">{{ Auth::user()->email }}</p>
                                    <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-700' : (Auth::user()->isServiceman() ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ ucfirst(strtolower(Auth::user()->user_type)) }}
                                    </span>
                                </div>
                                
                                <!-- Profile Link -->
                                <a href="{{ url('/profile') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-user-circle w-5 mr-3 text-gray-400"></i>
                                    <span class="font-medium">My Profile</span>
                                </a>
                                
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.analytics') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-chart-line w-5 mr-3 text-gray-400"></i>
                                        <span class="font-medium">Analytics</span>
                                    </a>
                                @endif
                                
                                <!-- Logout -->
                                <div class="border-t border-gray-200 mt-2 pt-2">
                                <form method="POST" action="{{ url('/logout') }}" class="block">
                                    @csrf
                                        <button type="submit" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                            <span class="font-medium">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        </div>
                    @endguest
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center space-x-2">
                    @auth
                        <x-notifications />
                    @endauth
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-blue-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-200 bg-white shadow-lg">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @guest
                    <a href="{{ url('/') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors {{ request()->is('/') ? 'text-blue-600 bg-blue-50' : '' }}">
                        <i class="fas fa-home w-5 mr-2"></i>Home
                    </a>
                    <a href="{{ url('/services') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors {{ request()->is('services*') ? 'text-blue-600 bg-blue-50' : '' }}">
                        <i class="fas fa-concierge-bell w-5 mr-2"></i>Services
                    </a>
                    <a href="{{ url('/about') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors {{ request()->is('about') ? 'text-blue-600 bg-blue-50' : '' }}">
                        <i class="fas fa-info-circle w-5 mr-2"></i>About
                    </a>
                    <a href="{{ url('/contact') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors {{ request()->is('contact') ? 'text-blue-600 bg-blue-50' : '' }}">
                        <i class="fas fa-envelope w-5 mr-2"></i>Contact
                    </a>
                    
                    <div class="border-t border-gray-200 pt-3 mt-3 space-y-2">
                        <a href="{{ url('/login') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-sign-in-alt w-5 mr-2"></i>Login
                        </a>
                        <a href="{{ url('/register') }}" class="flex items-center px-3 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-base font-medium shadow-md">
                            <i class="fas fa-user-plus w-5 mr-2"></i>Get Started
                        </a>
                    </div>
                @else
                    <div class="px-3 py-2.5 mb-2 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg flex items-center space-x-3">
                        <img src="{{ Auth::user()->profile_picture_url }}" 
                             alt="{{ Auth::user()->full_name }}" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-blue-200 shadow-sm flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ Auth::user()->email }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-700' : (Auth::user()->isServiceman() ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst(strtolower(Auth::user()->user_type)) }}
                            </span>
                        </div>
                    </div>
                    
                    <a href="{{ url('/dashboard') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                        <i class="fas fa-tachometer-alt w-5 mr-2"></i>Dashboard
                    </a>
                    
                    @if(Auth::user()->isClient())
                        <a href="{{ url('/services') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-concierge-bell w-5 mr-2"></i>Browse Services
                        </a>
                        <a href="{{ url('/service-requests') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-clipboard-list w-5 mr-2"></i>My Bookings
                        </a>
                    @elseif(Auth::user()->isServiceman())
                        <a href="{{ url('/service-requests') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-briefcase w-5 mr-2"></i>My Jobs
                        </a>
                        <a href="{{ route('profile.serviceman') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-user-cog w-5 mr-2"></i>My Profile
                        </a>
                    @elseif(Auth::user()->isAdmin())
                        <a href="{{ route('admin.service-requests') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-tasks w-5 mr-2"></i>Requests
                        </a>
                        <a href="{{ route('admin.categories') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-tags w-5 mr-2"></i>Categories
                        </a>
                        <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-users w-5 mr-2"></i>Users
                        </a>
                    @endif
                    
                    <a href="{{ url('/profile') }}" class="flex items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg text-base font-medium transition-colors">
                        <i class="fas fa-user w-5 mr-2"></i>Profile
                    </a>
                    
                    <form method="POST" action="{{ url('/logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-base font-medium transition-colors">
                            <i class="fas fa-sign-out-alt w-5 mr-2"></i>Logout
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <!-- ServiceMan Column -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">ServiceMan</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Connecting you with trusted service providers for all your needs.
                    </p>
                </div>

                <!-- Services Column -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Services</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('services') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Browse Categories
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('services') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Find Professionals
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('services') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Available Now
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Company Column -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Company</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('about') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                About Us
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('faq') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                FAQs
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Contact
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}?type=serviceman" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Become a Provider
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Support Column -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Support</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('faq') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Help Center
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Login
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="text-gray-600 hover:text-blue-600 transition-colors text-sm">
                                Register
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="border-t border-gray-300 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-gray-600 text-sm">
                        © 2025 ServiceMan. All rights reserved.
                    </p>
                    <p class="text-gray-600 text-sm">
                        Developed by <a href="https://www.sacscomputers.com/" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">SACS Computers</a> - IT in your palms
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>


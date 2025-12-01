@extends('layouts.app')

@section('title', 'Contact Us - ServiceMan')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Get in Touch</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Left Column - Contact Information -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
                    
                    <!-- Address -->
                    <div class="mb-6">
                        <div class="flex items-start mb-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Address</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    183 George Akume Way,<br>
                                    Opp. Wishden Filling Station,<br>
                                    Makurdi, Benue State, Nigeria
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <div class="flex items-start mb-3">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-envelope text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Email</h3>
                                <a href="mailto:info@sacscomputers.com" class="text-blue-600 hover:text-blue-700 font-semibold">
                                    info@sacscomputers.com
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="mb-6">
                        <div class="flex items-start mb-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-phone text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Phone</h3>
                                <div class="space-y-1">
                                    <a href="tel:+2347058270219" class="block text-blue-600 hover:text-blue-700 font-semibold">
                                        +234 705 827 0219
                                    </a>
                                    <a href="tel:+2349066274751" class="block text-blue-600 hover:text-blue-700 font-semibold">
                                        +234 906 627 4751
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Follow Us -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white hover:bg-blue-700 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-blue-400 rounded-lg flex items-center justify-center text-white hover:bg-blue-500 transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center text-white hover:bg-pink-700 transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center text-white hover:bg-blue-800 transition-colors">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Platform Developed By -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border-2 border-blue-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Platform Developed By</h3>
                        <p class="text-xl font-bold text-blue-600 mb-1">SACS Computers</p>
                        <p class="text-sm text-gray-600 italic">IT in your palms</p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Form -->
            <div>
                <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                    
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-2 border-green-500 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                <p class="text-green-800 font-semibold">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border-2 border-red-500 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-red-800 font-semibold mb-2">Please fix the following errors:</p>
                                    <ul class="list-disc list-inside text-red-700 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-900 mb-2">
                                Your Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Enter your full name">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-900 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="your.email@example.com">
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-bold text-gray-900 mb-2">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="What is this regarding?">
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-bold text-gray-900 mb-2">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      rows="6" 
                                      required
                                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                      placeholder="Tell us how we can help you..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-bold transition-colors shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Alternative Contact Methods -->
                <div class="mt-8 bg-white rounded-xl shadow-lg p-8 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Prefer to reach us directly?</h3>
                    
                    <div class="space-y-4">
                        <!-- Email -->
                        <a href="mailto:info@sacscomputers.com" 
                           class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <i class="fas fa-envelope text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 font-semibold">Email Us</p>
                                <p class="text-blue-600 font-bold">info@sacscomputers.com</p>
                            </div>
                        </a>

                        <!-- Phone -->
                        <a href="tel:+2347058270219" 
                           class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group">
                            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <i class="fas fa-phone text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 font-semibold">Call Us</p>
                                <p class="text-green-600 font-bold">+234 705 827 0219</p>
                            </div>
                        </a>

                        <!-- Visit -->
                        <div class="flex items-start p-4 bg-purple-50 rounded-lg">
                            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 font-semibold mb-1">Visit Us At</p>
                                <p class="text-purple-600 font-bold text-sm leading-relaxed">
                                    183 George Akume Way, Opp. Wishden Filling Station<br>
                                    Makurdi, Benue State, Nigeria
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

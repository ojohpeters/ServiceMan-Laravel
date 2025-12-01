@extends('layouts.app')

@section('title', 'Contact Us - ServiceMan')

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-emerald-50" style="margin-top: 80px;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        Get in <span class="text-blue-600">Touch</span>
                    </h1>
                    <p class="text-xl text-gray-600 leading-relaxed">
                        Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" style="margin-bottom: 80px;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Contact Information -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg shadow-sm border-0 h-full">
                    <div class="p-4 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Contact Information</h3>
                        
                        <!-- Address -->
                        <div class="flex gap-3 mb-6">
                            <div class="flex-shrink-0">
                                <div class="rounded-full bg-blue-100 p-3">
                                    <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="font-semibold text-gray-900 mb-1">Address</h6>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    183 George Akume Way,<br>
                                    Opp. Wishden Filling Station,<br>
                                    Makurdi, Benue State, Nigeria
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex gap-3 mb-6">
                            <div class="flex-shrink-0">
                                <div class="rounded-full bg-green-100 p-3">
                                    <i class="fas fa-envelope text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="font-semibold text-gray-900 mb-1">Email</h6>
                                <a href="mailto:info@sacscomputers.com" class="text-sm text-gray-600 hover:text-green-600 transition-colors block mb-1">
                                    info@sacscomputers.com
                                </a>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex gap-3 mb-6">
                            <div class="flex-shrink-0">
                                <div class="rounded-full bg-yellow-100 p-3">
                                    <i class="fas fa-phone text-yellow-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="font-semibold text-gray-900 mb-1">Phone</h6>
                                <a href="tel:+2347058270219" class="text-sm text-gray-600 hover:text-yellow-600 transition-colors block mb-1">
                                    +234 705 827 0219
                                </a>
                                <a href="tel:+2349066274751" class="text-sm text-gray-600 hover:text-yellow-600 transition-colors block">
                                    +234 906 627 4751
                                </a>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h6 class="font-semibold text-gray-900 mb-3">Follow Us</h6>
                            <div class="flex gap-2">
                                <a 
                                    href="https://web.facebook.com/people/SACS-Computers/100057023876823/" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="w-10 h-10 rounded-full border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center hover-lift"
                                >
                                    <i class="fab fa-facebook-f text-sm"></i>
                                </a>
                                <a 
                                    href="https://www.instagram.com/sacscomputers" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="w-10 h-10 rounded-full border-2 border-pink-600 text-pink-600 hover:bg-pink-600 hover:text-white transition-colors flex items-center justify-center hover-lift"
                                >
                                    <i class="fab fa-instagram text-sm"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Powered By -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Platform Developed By</p>
                                <a 
                                    href="https://www.sacscomputers.com/" 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    class="inline-block hover-lift"
                                >
                                    <div class="flex items-center justify-center gap-2 p-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg">
                                        <i class="fas fa-globe text-blue-600 text-lg"></i>
                                        <span class="font-bold text-gray-900">SACS Computers</span>
                                    </div>
                                </a>
                                <p class="text-sm text-gray-600 mt-2">
                                    IT in your palms
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-lg shadow-sm border-0">
                    <div class="p-4 md:p-6 lg:p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Send us a Message</h3>
                        
                        @if(session('success'))
                            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start">
                                <i class="fas fa-paper-plane text-green-600 mr-3 mt-0.5"></i>
                                <div>
                                    <p class="text-green-800 font-medium">
                                        Thank you for contacting us! We'll get back to you soon.
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                                    <div>
                                        <p class="text-red-800 font-semibold mb-2">Please fix the following errors:</p>
                                        <ul class="list-disc list-inside text-red-700 space-y-1 text-sm">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.submit') }}">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Your Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        placeholder="John Doe"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 mb-2">Email Address</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        placeholder="john@example.com"
                                    />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Subject</label>
                                <input
                                    type="text"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="How can we help you?"
                                />
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Message</label>
                                <textarea
                                    name="message"
                                    rows="6"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                    placeholder="Tell us more about your inquiry..."
                                >{{ old('message') }}</textarea>
                            </div>

                            <div>
                                <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg hover:shadow-xl">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Send Message
                                </button>
                            </div>
                        </form>

                        <!-- Alternative Contact Methods -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h5 class="font-semibold text-gray-900 mb-4">Prefer to reach us directly?</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <a 
                                    href="mailto:info@sacscomputers.com" 
                                    class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg text-decoration-none hover-lift group"
                                >
                                    <i class="fas fa-envelope text-blue-600 text-xl group-hover:scale-110 transition-transform"></i>
                                    <div>
                                        <div class="font-semibold text-gray-900">Email Us</div>
                                        <small class="text-gray-600">info@sacscomputers.com</small>
                                    </div>
                                </a>
                                <a 
                                    href="tel:+2347058270219" 
                                    class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg text-decoration-none hover-lift group"
                                >
                                    <i class="fas fa-phone text-green-600 text-xl group-hover:scale-110 transition-transform"></i>
                                    <div>
                                        <div class="font-semibold text-gray-900">Call Us</div>
                                        <small class="text-gray-600">+234 705 827 0219</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-sm border-0 overflow-hidden">
                <div class="p-0" style="height: 400px; background: #e9ecef;">
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt text-gray-400 text-5xl mb-4"></i>
                            <h5 class="text-gray-600 font-semibold mb-2">Visit Us At</h5>
                            <p class="text-gray-600">
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
@endsection

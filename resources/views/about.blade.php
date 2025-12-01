@extends('layouts.app')

@section('title', 'About Us - ServiceMan')
@section('description', 'Learn about ServiceMan, the leading platform connecting clients with skilled service professionals.')

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
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    About <span class="text-blue-600">ServiceMan</span>
                </h1>
                <p class="text-xl text-gray-600 mb-0">
                    Connecting clients with verified, skilled professionals for all their service needs
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Mission & Vision -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
            <!-- Mission -->
            <div class="bg-white rounded-lg shadow-sm border-0 h-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="rounded-full bg-blue-100 p-3 mr-3">
                            <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-0">Our Mission</h3>
                    </div>
                    <p class="text-gray-600 mb-0">
                        To revolutionize how people find and hire service professionals by creating a transparent, 
                        efficient, and trustworthy platform that benefits both clients and service providers. We believe 
                        quality service should be accessible, affordable, and reliable for everyone.
                    </p>
                </div>
            </div>

            <!-- Vision -->
            <div class="bg-white rounded-lg shadow-sm border-0 h-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="rounded-full bg-green-100 p-3 mr-3">
                            <i class="fas fa-bolt text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-0">Our Vision</h3>
                    </div>
                    <p class="text-gray-600 mb-0">
                        To become the leading platform connecting service professionals with clients across the region, 
                        empowering skilled workers while ensuring customers receive exceptional service every time. 
                        We envision a future where finding trusted help is just a few clicks away.
                    </p>
                </div>
            </div>
        </div>

        <!-- Core Values -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-center mb-8">Our Core Values</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Trust & Safety -->
                <div class="bg-white rounded-lg shadow-sm border-0 hover-lift h-full">
                    <div class="p-6 text-center">
                        <div class="rounded-full bg-blue-50 inline-flex items-center justify-center mb-4 w-20 h-20">
                            <i class="fas fa-shield-alt text-blue-600 text-4xl"></i>
                        </div>
                        <h5 class="font-bold mb-3">Trust & Safety</h5>
                        <p class="text-gray-600 text-sm mb-0">
                            All professionals are verified and vetted to ensure your safety and peace of mind.
                        </p>
                    </div>
                </div>

                <!-- Quality Service -->
                <div class="bg-white rounded-lg shadow-sm border-0 hover-lift h-full">
                    <div class="p-6 text-center">
                        <div class="rounded-full bg-emerald-50 inline-flex items-center justify-center mb-4 w-20 h-20">
                            <i class="fas fa-award text-emerald-600 text-4xl"></i>
                        </div>
                        <h5 class="font-bold mb-3">Quality Service</h5>
                        <p class="text-gray-600 text-sm mb-0">
                            We maintain high standards through ratings, reviews, and continuous quality monitoring.
                        </p>
                    </div>
                </div>

                <!-- Customer First -->
                <div class="bg-white rounded-lg shadow-sm border-0 hover-lift h-full">
                    <div class="p-6 text-center">
                        <div class="rounded-full bg-amber-50 inline-flex items-center justify-center mb-4 w-20 h-20">
                            <i class="fas fa-heart text-amber-600 text-4xl"></i>
                        </div>
                        <h5 class="font-bold mb-3">Customer First</h5>
                        <p class="text-gray-600 text-sm mb-0">
                            Your satisfaction is our priority. We're here to ensure you get the best service experience.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-white rounded-3xl shadow-sm p-8 lg:p-12 mb-12">
            <h2 class="text-2xl font-bold text-center mb-12">How ServiceMan Works</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="rounded-full bg-blue-100 inline-flex items-center justify-center mb-4 w-16 h-16">
                        <span class="font-bold text-blue-600 text-2xl">1</span>
                    </div>
                    <h6 class="font-bold mb-2">Search & Browse</h6>
                    <p class="text-gray-600 text-sm">Find professionals by category or search for specific services</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="rounded-full bg-blue-100 inline-flex items-center justify-center mb-4 w-16 h-16">
                        <span class="font-bold text-blue-600 text-2xl">2</span>
                    </div>
                    <h6 class="font-bold mb-2">Book Service</h6>
                    <p class="text-gray-600 text-sm">Select your preferred professional and pay a small booking fee</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="rounded-full bg-blue-100 inline-flex items-center justify-center mb-4 w-16 h-16">
                        <span class="font-bold text-blue-600 text-2xl">3</span>
                    </div>
                    <h6 class="font-bold mb-2">Get Estimate</h6>
                    <p class="text-gray-600 text-sm">Serviceman reviews your request and provides a cost estimate</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="rounded-full bg-green-100 inline-flex items-center justify-center mb-4 w-16 h-16">
                        <span class="font-bold text-green-600 text-2xl">4</span>
                    </div>
                    <h6 class="font-bold mb-2">Service Delivered</h6>
                    <p class="text-gray-600 text-sm">Approve the price, pay, and enjoy professional service</p>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-center mb-8">Why Choose ServiceMan?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Verified Professionals -->
                <div class="flex gap-4 p-6 bg-white rounded-3xl shadow-sm h-full">
                    <div class="flex-shrink-0">
                        <div class="rounded bg-blue-100 p-3">
                            <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="font-bold mb-2">Verified Professionals</h6>
                        <p class="text-gray-600 text-sm mb-0">
                            Every serviceman on our platform is vetted, verified, and approved by our admin team to ensure quality and reliability.
                        </p>
                    </div>
                </div>

                <!-- Transparent Pricing -->
                <div class="flex gap-4 p-6 bg-white rounded-3xl shadow-sm h-full">
                    <div class="flex-shrink-0">
                        <div class="rounded bg-green-100 p-3">
                            <i class="fas fa-award text-green-600 text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="font-bold mb-2">Transparent Pricing</h6>
                        <p class="text-gray-600 text-sm mb-0">
                            Get detailed estimates before work begins. Know exactly what you're paying for with no hidden fees.
                        </p>
                    </div>
                </div>

                <!-- Secure Payments -->
                <div class="flex gap-4 p-6 bg-white rounded-3xl shadow-sm h-full">
                    <div class="flex-shrink-0">
                        <div class="rounded bg-amber-100 p-3">
                            <i class="fas fa-users text-amber-600 text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="font-bold mb-2">Secure Payments</h6>
                        <p class="text-gray-600 text-sm mb-0">
                            All payments are processed securely through Paystack. Your financial information is always protected.
                        </p>
                    </div>
                </div>

                <!-- Customer Support -->
                <div class="flex gap-4 p-6 bg-white rounded-3xl shadow-sm h-full">
                    <div class="flex-shrink-0">
                        <div class="rounded bg-blue-100 p-3">
                            <i class="fas fa-heart text-red-600 text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="font-bold mb-2">Customer Support</h6>
                        <p class="text-gray-600 text-sm mb-0">
                            Our admin team monitors all service requests and is ready to help resolve any issues promptly.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center py-8">
            <div class="bg-gradient-to-br from-blue-50 to-emerald-50 rounded-lg shadow-sm p-8 lg:p-12">
                <h2 class="text-2xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-gray-600 mb-6">
                    Join our growing community of satisfied customers who found their perfect service provider
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('services') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors">
                        Find a Service Provider
                    </a>
                    <a href="{{ route('register') }}?type=serviceman" class="border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-8 py-4 rounded-lg text-lg font-semibold transition-colors">
                        Become a Provider
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

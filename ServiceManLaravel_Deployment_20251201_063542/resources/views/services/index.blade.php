@extends('layouts.app')

@section('title', 'Services - ServiceMan')
@section('description', 'Browse our professional service categories and find the right expert for your needs.')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Our Services</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Choose from our professional service categories and connect with skilled experts
        </p>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($categories as $category)
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-{{ $category->icon ?? 'tools' }} text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->servicemen_count }} professionals</p>
                    </div>
                </div>
                
                @if($category->description)
                    <p class="text-gray-600 mb-4">{{ $category->description }}</p>
                @endif
                
                <div class="flex items-center justify-between">
                    <a href="{{ route('services.category', $category) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        View Services <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                    @auth
                        <a href="{{ route('services.category', $category) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            Book Now
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            Book Now
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-tools text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">No services available at the moment.</p>
            </div>
        @endforelse
    </div>

    <!-- CTA Section -->
    <div class="mt-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-8 text-center text-white">
        <h2 class="text-2xl font-bold mb-4">Need a Custom Service?</h2>
        <p class="text-blue-100 mb-6 max-w-2xl mx-auto">
            Can't find what you're looking for? Contact us and we'll help you find the right professional for your specific needs.
        </p>
        @auth
            <a href="{{ route('services') }}" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Request Custom Service
            </a>
        @else
            <a href="{{ route('register') }}" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Get Started
            </a>
        @endauth
    </div>
</div>
@endsection

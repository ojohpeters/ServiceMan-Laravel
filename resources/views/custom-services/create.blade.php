@extends('layouts.app')

@section('title', 'Request Custom Service')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-orange-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-2xl animate-pulse">
                <i class="fas fa-lightbulb text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                Request Custom Service
            </h1>
            <p class="text-gray-600 text-lg">Don't see your service category? Let us know what you do!</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-2xl shadow-md animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 text-xl mt-1"></i>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-orange-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-file-alt mr-3"></i>Service Request Form
                </h2>
                <p class="text-purple-100 text-sm mt-1">Tell us about the service you want to provide</p>
            </div>

            <form action="{{ route('custom-services.store') }}" method="POST" class="p-8">
                @csrf

                <div class="space-y-6">
                    <!-- Service Name -->
                    <div>
                        <label for="service_name" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-briefcase mr-2 text-purple-600"></i>
                            Service Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="service_name" 
                               name="service_name" 
                               required
                               value="{{ old('service_name') }}"
                               maxlength="255"
                               placeholder="e.g., HVAC Services, Locksmith, Tiling, Landscaping, etc."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            What do you call your service? Keep it short and clear.
                        </p>
                        @error('service_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Service Description -->
                    <div>
                        <label for="service_description" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-align-left mr-2 text-pink-600"></i>
                            Service Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="service_description" 
                                  name="service_description" 
                                  rows="5" 
                                  required
                                  maxlength="1000"
                                  placeholder="Describe what services you provide, what makes you qualified, and what type of work you do. Be specific and detailed."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none">{{ old('service_description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Provide detailed information about your service, qualifications, and specialties.
                        </p>
                        @error('service_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Why Needed -->
                    <div>
                        <label for="why_needed" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-question-circle mr-2 text-orange-600"></i>
                            Why is this service needed? <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                        </label>
                        <textarea id="why_needed" 
                                  name="why_needed" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Why do you think this service should be added to our platform? Is there demand for it?"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none">{{ old('why_needed') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Help us understand the market need for this service.
                        </p>
                        @error('why_needed')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Target Market -->
                    <div>
                        <label for="target_market" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-users mr-2 text-blue-600"></i>
                            Target Market/Customers <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                        </label>
                        <textarea id="target_market" 
                                  name="target_market" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Who are your typical customers? Homeowners, businesses, industries, etc."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none">{{ old('target_market') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Describe your ideal customers and target market.
                        </p>
                        @error('target_market')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Information Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-xl p-4 mt-8">
                    <p class="text-sm text-blue-900 font-semibold mb-2">
                        <i class="fas fa-info-circle mr-2"></i>What happens next?
                    </p>
                    <ul class="text-xs text-blue-800 space-y-2 ml-6">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>Your request will be submitted to the admin team</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>Admin will review and decide if the service can be added</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>You'll receive a notification with the decision and next steps</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>If approved, you can apply for the category on your profile</span>
                        </li>
                    </ul>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t-2 border-gray-200">
                    <a href="{{ route('custom-services.index') }}" 
                       class="flex-1 flex items-center justify-center px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center px-6 py-4 bg-gradient-to-r from-purple-600 via-pink-600 to-orange-600 hover:from-purple-700 hover:via-pink-700 hover:to-orange-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
}
</style>
@endsection


@extends('layouts.app')

@section('title', 'Physical Verification Required')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-yellow-50 to-red-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-orange-600 via-red-600 to-pink-600 px-8 py-12 text-center">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl">
                    <i class="fas fa-user-clock text-orange-600 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">
                    Physical Verification Required
                </h1>
                <p class="text-orange-100 text-lg">
                    Your account is pending admin approval
                </p>
            </div>

            <!-- Content -->
            <div class="px-8 py-10">
                <!-- Welcome Message -->
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-2xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-hand-wave text-blue-600 mr-3"></i>
                        Welcome, {{ auth()->user()->full_name }}!
                    </h2>
                    <p class="text-gray-700 leading-relaxed">
                        Thank you for registering as a serviceman on our platform. We're excited to have you join our team!
                    </p>
                </div>

                <!-- Verification Steps -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-clipboard-check text-green-600 mr-3"></i>
                        Next Steps for Approval
                    </h3>

                    <div class="space-y-4">
                        <!-- Step 1 -->
                        <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-2xl border-2 border-green-200">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-600 to-green-700 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                1
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 mb-1">Profile Review</h4>
                                <p class="text-sm text-gray-700">
                                    Our admin team is currently reviewing your profile and credentials. This usually takes 24-48 hours.
                                </p>
                            </div>
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-2xl border-2 border-yellow-200">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                2
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 mb-1">Physical Verification</h4>
                                <p class="text-sm text-gray-700">
                                    Once approved, you'll be contacted via phone or email to schedule a physical verification meeting at our office. Please bring:
                                </p>
                                <ul class="mt-2 space-y-1 text-sm text-gray-700">
                                    <li class="flex items-center">
                                        <i class="fas fa-id-card text-yellow-600 mr-2"></i>
                                        Valid government-issued ID
                                    </li>
                                    <li class="flex items-center">
                                        <i class="fas fa-certificate text-yellow-600 mr-2"></i>
                                        Professional certifications (if applicable)
                                    </li>
                                    <li class="flex items-center">
                                        <i class="fas fa-file-alt text-yellow-600 mr-2"></i>
                                        Proof of experience or references
                                    </li>
                                </ul>
                            </div>
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-start space-x-4 p-4 bg-purple-50 rounded-2xl border-2 border-purple-200">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-full flex items-center justify-center text-white font-bold shadow-md">
                                3
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 mb-1">Account Activation</h4>
                                <p class="text-sm text-gray-700">
                                    After successful verification, your account will be activated and you'll receive an email notification. You can then login and start accepting jobs!
                                </p>
                            </div>
                            <i class="fas fa-hourglass-half text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-200 rounded-2xl p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-phone-alt text-blue-600 mr-3"></i>
                        Contact Information
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We will contact you using the information you provided:
                    </p>
                    <div class="space-y-2 pl-4">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-envelope text-blue-600 w-6 mr-3"></i>
                            <span class="font-semibold">Email:</span>
                            <span class="ml-2">{{ auth()->user()->email }}</span>
                        </div>
                        @if(auth()->user()->phone_number)
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-phone text-green-600 w-6 mr-3"></i>
                                <span class="font-semibold">Phone:</span>
                                <span class="ml-2">{{ auth()->user()->phone_number }}</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        Please ensure your contact information is correct. If you need to update it, please contact our support team.
                    </p>
                </div>

                <!-- Important Notice -->
                <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-r-2xl p-6 mb-8">
                    <h3 class="text-lg font-bold text-red-900 mb-2 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        Important Notice
                    </h3>
                    <ul class="space-y-2 text-sm text-red-800">
                        <li class="flex items-start">
                            <i class="fas fa-lock text-red-600 mr-3 mt-1"></i>
                            <span>You cannot login or accept jobs until your account is approved</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-bell text-red-600 mr-3 mt-1"></i>
                            <span>You will receive email and in-app notifications once approved</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-calendar-check text-red-600 mr-3 mt-1"></i>
                            <span>Physical verification is mandatory for all servicemen</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-red-600 mr-3 mt-1"></i>
                            <span>This process ensures quality and safety for our clients</span>
                        </li>
                    </ul>
                </div>

                <!-- FAQ Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-question-circle text-purple-600 mr-3"></i>
                        Frequently Asked Questions
                    </h3>
                    
                    <div class="space-y-3">
                        <details class="bg-gray-50 rounded-xl p-4 cursor-pointer hover:bg-gray-100 transition-colors">
                            <summary class="font-semibold text-gray-900">How long does the approval process take?</summary>
                            <p class="mt-2 text-sm text-gray-700 pl-4">
                                Profile review typically takes 24-48 hours. Physical verification scheduling will depend on your availability and our office hours.
                            </p>
                        </details>

                        <details class="bg-gray-50 rounded-xl p-4 cursor-pointer hover:bg-gray-100 transition-colors">
                            <summary class="font-semibold text-gray-900">Where is the physical verification done?</summary>
                            <p class="mt-2 text-sm text-gray-700 pl-4">
                                Physical verification is conducted at our main office. The exact address and directions will be provided when we contact you.
                            </p>
                        </details>

                        <details class="bg-gray-50 rounded-xl p-4 cursor-pointer hover:bg-gray-100 transition-colors">
                            <summary class="font-semibold text-gray-900">What if I don't hear from you?</summary>
                            <p class="mt-2 text-sm text-gray-700 pl-4">
                                If you don't receive any communication within 72 hours, please contact our support team using the contact form or phone number on our website.
                            </p>
                        </details>

                        <details class="bg-gray-50 rounded-xl p-4 cursor-pointer hover:bg-gray-100 transition-colors">
                            <summary class="font-semibold text-gray-900">Can I update my profile while pending?</summary>
                            <p class="mt-2 text-sm text-gray-700 pl-4">
                                Yes, you can update your profile information anytime. Any changes will be reviewed by our admin team.
                            </p>
                        </details>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('profile.serviceman') }}" 
                       class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl text-center">
                        <i class="fas fa-user-edit mr-2"></i>
                        Update My Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 mb-2">
                Need help? Contact us:
            </p>
            <div class="flex justify-center space-x-6">
                <a href="{{ route('contact') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact Form
                </a>
                <a href="tel:+234-XXX-XXX-XXXX" class="text-green-600 hover:text-green-800 font-semibold">
                    <i class="fas fa-phone mr-2"></i>
                    Call Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


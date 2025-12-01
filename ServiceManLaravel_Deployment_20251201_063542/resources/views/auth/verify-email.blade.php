@extends('layouts.app')

@section('title', 'Verify Email - ServiceMan')
@section('description', 'Verify your email address to complete your ServiceMan account setup.')

@section('content')
<div x-data="verifyEmail()" x-init="checkVerificationStatus()" class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-envelope-open text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-gray-900">Verify Your Email</h2>
            <p class="mt-2 text-sm text-gray-600">
                We've sent a verification link to your email address.
            </p>
        </div>

        <!-- Verification Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Loading State -->
            <div x-show="loading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Checking verification status...</p>
            </div>

            <!-- Email Input Form -->
            <div x-show="!loading && !verificationSent" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        x-model="email"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        placeholder="Enter your email address"
                        :class="{ 'border-red-500': errors.email }"
                    >
                    <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
                </div>

                <button 
                    @click="resendVerificationEmail"
                    :disabled="resendLoading"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                >
                    <span x-show="!resendLoading">Resend Verification Email</span>
                    <span x-show="resendLoading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Sending...
                    </span>
                </button>
            </div>

            <!-- Success Message -->
            <div x-show="verificationSent" class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Email Sent!</h3>
                <p class="text-gray-600 mb-6">
                    We've sent a new verification link to <span class="font-medium" x-text="email"></span>. 
                    Please check your inbox and click the link to verify your email.
                </p>
                <button 
                    @click="verificationSent = false"
                    class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
                >
                    Send to a different email
                </button>
            </div>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">What's next?</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>1. Check your email inbox (and spam folder)</li>
                    <li>2. Click the verification link in the email</li>
                    <li>3. Return here and try logging in again</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 space-y-3">
                <!-- Skip Verification for Demo -->
                <button 
                    @click="skipVerification"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <i class="fas fa-forward mr-2"></i>
                    Skip Verification (Demo)
                </button>
                
                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ url('/login') }}" class="text-blue-600 hover:text-blue-700 font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                Didn't receive the email? Check your spam folder or 
                <a href="#" @click="showHelp = !showHelp" class="text-blue-600 hover:text-blue-700 font-medium">
                    contact support
                </a>
            </p>
            
            <!-- Help Modal -->
            <div x-show="showHelp" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showHelp = false">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Need Help?</h3>
                    <p class="text-gray-600 mb-6">
                        If you're having trouble receiving verification emails, please try:
                    </p>
                    <ul class="text-gray-600 space-y-2 mb-6">
                        <li>• Check your spam/junk folder</li>
                        <li>• Make sure you entered the correct email address</li>
                        <li>• Wait a few minutes for the email to arrive</li>
                        <li>• Try resending the verification email</li>
                    </ul>
                    <div class="flex justify-end">
                        <button 
                            @click="showHelp = false"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Got it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function verifyEmail() {
    return {
        email: '',
        loading: false,
        resendLoading: false,
        verificationSent: false,
        showHelp: false,
        errors: {},

        checkVerificationStatus() {
            // Check if there's an email parameter in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');
            const fromLogin = urlParams.get('from_login');
            
            if (email) {
                this.email = email;
            }
            
            // If coming from login, restore the auth token
            if (fromLogin === 'true') {
                const tempToken = localStorage.getItem('temp_auth_token');
                if (tempToken) {
                    localStorage.setItem('auth_token', tempToken);
                    localStorage.removeItem('temp_auth_token');
                }
            }
        },

        async resendVerificationEmail() {
            if (!this.email) {
                this.errors.email = 'Email address is required';
                return;
            }

            this.resendLoading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/auth/resend-verification-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: this.email })
                });

                const data = await response.json();

                if (response.ok) {
                    this.verificationSent = true;
                    this.showNotification('Verification email sent successfully!', 'success');
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showNotification(data.message || 'Failed to send verification email', 'error');
                    }
                }
            } catch (error) {
                console.error('Error sending verification email:', error);
                this.showNotification('Network error. Please try again.', 'error');
            } finally {
                this.resendLoading = false;
            }
        },

        async skipVerification() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                // Mark user as verified via API
                const response = await fetch('/api/auth/verify-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        uid: 1, // For demo purposes
                        token: 'demo_token'
                    })
                });

                if (response.ok) {
                    this.showNotification('Verification skipped! Redirecting to dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    // Even if API fails, redirect to dashboard for demo
                    this.showNotification('Redirecting to dashboard...', 'info');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                }
            } catch (error) {
                console.error('Error skipping verification:', error);
                // Redirect anyway for demo purposes
                this.showNotification('Redirecting to dashboard...', 'info');
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1000);
            }
        },

        showNotification(message, type = 'info') {
            // Create a simple notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
            
            // Set colors based on type
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            notification.className += ` ${colors[type] || colors.info}`;
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection

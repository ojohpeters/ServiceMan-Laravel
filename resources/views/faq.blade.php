@extends('layouts.app')

@section('title', 'Frequently Asked Questions - ServiceMan')
@section('description', 'Find answers to common questions about using ServiceMan platform.')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="faqPage()">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-emerald-50" style="margin-top: 80px;">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center justify-center bg-blue-100 rounded-full mb-6 w-20 h-20">
                    <i class="fas fa-question-circle text-blue-600 text-4xl"></i>
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Frequently Asked Questions
                </h1>
                <p class="text-xl text-gray-600 mb-0">
                    Find answers to common questions about using ServiceMan
                </p>
            </div>
        </div>
    </div>

    <!-- FAQ Content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 mb-20">
        <!-- For Clients -->
        <div class="mb-12">
            <h2 class="text-xl font-bold mb-6 text-blue-600">For Clients</h2>
            <div class="space-y-3">
                <template x-for="(faq, index) in clientFAQs" :key="index">
                    <div class="bg-white rounded-lg shadow-sm border-0">
                        <div class="bg-white border-0">
                            <button
                                @click="toggleFAQ('client', index)"
                                class="w-full text-left flex justify-between items-center p-4 hover:bg-gray-50 transition-colors"
                                :class="openIndex === `client-${index}` ? 'text-blue-600' : 'text-gray-900'"
                            >
                                <span class="font-semibold pr-4" x-text="faq.q"></span>
                                <i 
                                    class="fas fa-chevron-down transition-transform flex-shrink-0"
                                    :class="openIndex === `client-${index}` ? 'transform rotate-180' : ''"
                                ></i>
                            </button>
                        </div>
                        <div 
                            x-show="openIndex === `client-${index}`"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="px-4 pb-4"
                        >
                            <p class="text-gray-600 mb-0" x-html="faq.a"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- For Service Providers -->
        <div class="mb-12">
            <h2 class="text-xl font-bold mb-6 text-blue-600">For Service Providers</h2>
            <div class="space-y-3">
                <template x-for="(faq, index) in providerFAQs" :key="index">
                    <div class="bg-white rounded-lg shadow-sm border-0">
                        <div class="bg-white border-0">
                            <button
                                @click="toggleFAQ('provider', index)"
                                class="w-full text-left flex justify-between items-center p-4 hover:bg-gray-50 transition-colors"
                                :class="openIndex === `provider-${index}` ? 'text-blue-600' : 'text-gray-900'"
                            >
                                <span class="font-semibold pr-4" x-text="faq.q"></span>
                                <i 
                                    class="fas fa-chevron-down transition-transform flex-shrink-0"
                                    :class="openIndex === `provider-${index}` ? 'transform rotate-180' : ''"
                                ></i>
                            </button>
                        </div>
                        <div 
                            x-show="openIndex === `provider-${index}`"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="px-4 pb-4"
                        >
                            <p class="text-gray-600 mb-0" x-text="faq.a"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- General -->
        <div class="mb-12">
            <h2 class="text-xl font-bold mb-6 text-blue-600">General</h2>
            <div class="space-y-3">
                <template x-for="(faq, index) in generalFAQs" :key="index">
                    <div class="bg-white rounded-lg shadow-sm border-0">
                        <div class="bg-white border-0">
                            <button
                                @click="toggleFAQ('general', index)"
                                class="w-full text-left flex justify-between items-center p-4 hover:bg-gray-50 transition-colors"
                                :class="openIndex === `general-${index}` ? 'text-blue-600' : 'text-gray-900'"
                            >
                                <span class="font-semibold pr-4" x-text="faq.q"></span>
                                <i 
                                    class="fas fa-chevron-down transition-transform flex-shrink-0"
                                    :class="openIndex === `general-${index}` ? 'transform rotate-180' : ''"
                                ></i>
                            </button>
                        </div>
                        <div 
                            x-show="openIndex === `general-${index}`"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="px-4 pb-4"
                        >
                            <p class="text-gray-600 mb-0" x-text="faq.a"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="text-center py-8">
            <div class="bg-white rounded-lg shadow-sm border-0 p-8 lg:p-12">
                <h3 class="text-xl font-bold mb-4">Still have questions?</h3>
                <p class="text-gray-600 mb-6">
                    Can't find the answer you're looking for? Our support team is here to help.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        Contact Support
                    </a>
                    <a href="{{ route('services') }}" class="border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-6 py-3 rounded-lg font-semibold transition-colors">
                        Browse Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function faqPage() {
    return {
        openIndex: 'client-0', // First FAQ open by default
        
        clientFAQs: [
            {
                q: "How do I book a service?",
                a: "Simply browse our categories or search for a specific service, select your preferred professional, fill in the service details, and pay a small booking fee (₦2,000 regular or ₦5,000 for emergency within 2 days). The serviceman will then review your request and provide a cost estimate."
            },
            {
                q: "What payment methods do you accept?",
                a: "We use Paystack for secure payment processing. You can pay with debit cards, credit cards, bank transfers, and other payment methods supported by Paystack. All transactions are encrypted and secure."
            },
            {
                q: "What is the booking fee?",
                a: "The booking fee is ₦2,000 for regular bookings (2+ days in advance) and ₦5,000 for emergency services (less than 2 days). This fee confirms your booking and allows the serviceman to review your request and provide an estimate."
            },
            {
                q: "How does the pricing work?",
                a: "After you book, the serviceman reviews your request and provides a detailed estimate based on the work required. You'll see the full cost breakdown before making the final payment, ensuring complete transparency."
            },
            {
                q: "Can I cancel a booking?",
                a: "Yes, you can cancel bookings at certain stages. However, the booking fee is non-refundable as it compensates the serviceman for reviewing your request. You can cancel before the serviceman submits an estimate."
            },
            {
                q: "How do I rate a serviceman?",
                a: "After the service is completed, you'll be able to submit a rating and review. This helps other customers make informed decisions and helps maintain quality on our platform."
            },
            {
                q: "What if I'm not satisfied with the service?",
                a: "If you have any issues with the service, you can report them while rating and reviewing the serviceman after completion. You can also contact us directly through our <a href=\"{{ route('contact') }}\" class=\"text-blue-600 hover:text-blue-700 font-semibold no-underline\">contact page</a>. We take quality seriously and will work to resolve any problems."
            }
        ],
        
        providerFAQs: [
            {
                q: "How do I become a service provider?",
                a: "Click 'Become a Provider' on the homepage, fill out the registration form with your skills and experience, and submit your application. Our admin team will review your profile and approve qualified professionals."
            },
            {
                q: "How do I get paid?",
                a: "Clients pay through our secure Paystack integration. The payment is processed after you complete the job and the client confirms. You receive the service cost (your estimate) while the platform retains a small fee."
            },
            {
                q: "Can I set my own prices?",
                a: "Yes! When you receive a service request, you review the details and submit your own cost estimate based on the work required. Clients see this estimate (plus a small platform fee) before approving."
            },
            {
                q: "What if I need to decline a job?",
                a: "You can view job requests on your dashboard and choose which ones to accept. You're not obligated to take every request, but maintaining a good acceptance rate helps build your reputation."
            },
            {
                q: "How does the backup serviceman system work?",
                a: "For important jobs, admin may assign a backup serviceman. If you're unable to complete the job, the backup can step in. This ensures clients always receive service and helps maintain platform reliability."
            }
        ],
        
        generalFAQs: [
            {
                q: "Is ServiceMan available in my area?",
                a: "We're currently expanding our service areas. When booking, you'll provide your address and we'll match you with available professionals in your location."
            },
            {
                q: "How are servicemen verified?",
                a: "All servicemen must submit their skills, experience, and credentials during registration. Our admin team reviews each application, verifies qualifications, and only approves qualified professionals."
            },
            {
                q: "What categories of services are available?",
                a: "We offer a wide range of services including Plumbing, Electrical work, HVAC, Painting, Carpentry, and more. Browse our categories page to see all available services."
            },
            {
                q: "How do notifications work?",
                a: "You'll receive notifications for important updates like booking confirmations, estimate submissions, payment confirmations, and service updates. Check the notifications icon in your dashboard to stay updated."
            },
            {
                q: "Is my personal information safe?",
                a: "Yes, we take data privacy seriously. Your personal information is encrypted and stored securely. We never share your data with third parties without your consent."
            }
        ],
        
        toggleFAQ(category, index) {
            const key = `${category}-${index}`;
            this.openIndex = this.openIndex === key ? null : key;
        },
        
    }
}
</script>
@endpush
@endsection


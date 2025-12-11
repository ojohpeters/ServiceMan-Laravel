@extends('layouts.app')

@section('title', $user->full_name . ' - ServiceMan')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8 overflow-x-hidden">
    <!-- Admin Controls (Only visible to admins) -->
    @auth
        @if(auth()->user()->isAdmin())
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                            Admin Controls
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            <!-- Approval Status -->
                            @if($user->is_approved)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>Approved
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-2"></i>Pending Approval
                                </span>
                            @endif
                            
                            <!-- Email Verification Status -->
                            @if($user->is_email_verified)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-envelope-check mr-2"></i>Email Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-envelope-times mr-2"></i>Email Not Verified
                                </span>
                            @endif
                            
                            <!-- Category Status -->
                            @if($user->servicemanProfile->category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                    <i class="fas fa-tag mr-2"></i>{{ $user->servicemanProfile->category->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>No Category Assigned
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 flex-wrap">
                        @if($user->is_approved)
                            <form method="POST" action="{{ route('admin.servicemen.revoke-approval', $user) }}" 
                                  onsubmit="return confirm('Are you sure you want to REVOKE approval for {{ $user->full_name }}? They will be immediately logged out and unable to login until re-approved.');">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                    <i class="fas fa-user-times mr-2"></i>Revoke Approval
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.servicemen.approve', $user) }}" 
                                  onsubmit="return confirm('Are you sure you want to APPROVE {{ $user->full_name }}? They will be able to login and accept jobs.');">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                    <i class="fas fa-user-check mr-2"></i>Approve Serviceman
                                </button>
                            </form>
                        @endif
                        
                        @if(!$user->servicemanProfile->category)
                            <a href="{{ route('admin.pending-servicemen') }}" 
                               class="inline-flex items-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                <i class="fas fa-tag mr-2"></i>Assign Category
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.servicemen') }}" 
                           class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Servicemen
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endauth
    
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 lg:p-8 mb-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4 lg:gap-6">
            <!-- Profile Picture -->
            <div class="flex-shrink-0 mx-auto lg:mx-0">
                <img src="{{ $user->profile_picture_url }}" 
                     alt="{{ $user->full_name }}" 
                     class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-blue-100 shadow-lg">
            </div>
            
            <!-- Profile Info -->
            <div class="flex-1 min-w-0 text-center lg:text-left w-full">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $user->full_name }}</h1>
                <p class="text-base sm:text-lg text-gray-600 mt-1">{{ $user->servicemanProfile->category->name ?? 'Professional' }}</p>
                
                @if($user->ratingsReceived->count() > 0)
                    <div class="flex items-center justify-center lg:justify-start mt-2 flex-wrap gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $user->ratingsReceived->avg('rating') ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                        <span class="text-sm text-gray-600">
                            {{ number_format($user->ratingsReceived->avg('rating'), 1) }} ({{ $user->ratingsReceived->count() }} reviews)
                        </span>
                    </div>
                @else
                    <p class="text-gray-500 mt-2 text-sm">No reviews yet</p>
                @endif
                
                <div class="flex flex-wrap items-center justify-center lg:justify-start mt-4 gap-2">
                    <span class="px-3 py-1 text-xs sm:text-sm font-medium rounded-full {{ $user->servicemanProfile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->servicemanProfile->is_available ? 'Available' : 'Busy' }}
                    </span>
                    
                    @if($user->servicemanProfile->category && $user->servicemanProfile->rating)
                        @php
                            $rank = $user->servicemanProfile->getCategoryRank();
                        @endphp
                        <span class="px-3 py-1 text-xs sm:text-sm font-medium rounded-full bg-purple-100 text-purple-800">
                            <i class="fas fa-trophy mr-1"></i>
                            Rank #{{ $rank }} in {{ $user->servicemanProfile->category->name }}
                        </span>
                    @endif
                    
                    @if($user->servicemanProfile->experience_years)
                        <span class="text-xs sm:text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $user->servicemanProfile->experience_years }} years experience
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Action Button - Full width on mobile, positioned on desktop -->
            <div class="w-full lg:w-auto lg:flex-shrink-0 lg:text-right mt-4 lg:mt-0">
            @auth
                @if(auth()->user()->isAdmin())
                    <!-- Admin viewing - show quick actions -->
                    <div class="flex flex-col gap-2">
                        @if($user->is_approved)
                            <span class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>Approved
                            </span>
                        @else
                            <span class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-semibold">
                                <i class="fas fa-clock mr-2"></i>Pending Approval
                            </span>
                        @endif
                        <p class="text-xs text-gray-500 text-center">Use admin controls above for actions</p>
                    </div>
                @elseif(auth()->user()->isClient())
                    @php
                        $canBook = isset($isAvailableForBooking) ? $isAvailableForBooking : ($user->isServiceman() ? $user->isAvailableForBooking() : false);
                    @endphp
                    @if($canBook)
                        <a href="{{ route('service-requests.create') }}?serviceman_id={{ $user->id }}" 
                           class="w-full sm:w-auto block sm:inline-block text-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                            <i class="fas fa-calendar-check mr-2"></i>Book Now
                        </a>
                    @else
                        <button disabled class="w-full sm:w-auto block sm:inline-block text-center bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed whitespace-nowrap" title="This serviceman is currently unavailable for booking">
                            <i class="fas fa-ban mr-2"></i>Currently Unavailable
                        </button>
                    @endif
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
                        <p class="text-xs sm:text-sm text-yellow-800 text-center sm:text-left">
                            <i class="fas fa-info-circle mr-2"></i>Only clients can book services
                        </p>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="w-full sm:w-auto block sm:inline-block text-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Book
                </a>
            @endauth
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Availability Calendar - Moved to main content for more space -->
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                    Availability Calendar
                </h3>
                <div id="calendar-container" class="availability-calendar-wrapper mb-4"></div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center flex-wrap gap-4 text-xs sm:text-sm">
                            <div class="flex items-center">
                                <div class="w-5 h-5 bg-gray-100 border-2 border-gray-300 rounded mr-2 flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 bg-red-500 rounded mr-2 flex items-center justify-center">
                                    <i class="fas fa-times text-white text-xs"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Busy</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 sm:text-right">All dates are available unless marked as busy</p>
                    </div>
                </div>
            </div>

            <!-- About -->
            @if($user->servicemanProfile->bio)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">About</h2>
                    <p class="text-gray-700">{{ $user->servicemanProfile->bio }}</p>
                </div>
            @endif

            <!-- Skills -->
            @if($user->servicemanProfile->skills)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Skills & Expertise</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $user->servicemanProfile->skills) as $skill)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                {{ trim($skill) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Reviews -->
            @if($user->ratingsReceived->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviews</h2>
                    <div class="space-y-4">
                        @foreach($user->ratingsReceived->take(5) as $rating)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600">{{ $rating->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if($rating->comment)
                                    <p class="text-gray-700">{{ $rating->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

            <!-- Service Category -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Category</h3>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-{{ $user->servicemanProfile->category->icon ?? 'tools' }} text-blue-600"></i>
                    </div>
                    <span class="text-gray-700">{{ $user->servicemanProfile->category->name ?? 'Professional' }}</span>
                </div>
                @if($user->servicemanProfile->category && $user->servicemanProfile->rating)
                    @php
                        $rank = $user->servicemanProfile->getCategoryRank();
                    @endphp
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Category Ranking:</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm font-semibold">
                                #{{ $rank }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Availability Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Status</h3>
                <div class="flex items-center">
                    @php
                        $isBusyToday = isset($isBusyToday) ? $isBusyToday : ($user->isServiceman() ? $user->isBusyOnDate(\Carbon\Carbon::today()) : false);
                        $isActuallyAvailable = $user->servicemanProfile->is_available && !$isBusyToday;
                    @endphp
                    <div class="w-3 h-3 rounded-full {{ $isActuallyAvailable ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                    <span class="text-gray-700 font-medium">
                        {{ $isActuallyAvailable ? 'Currently Available' : 'Currently Unavailable' }}
                    </span>
                </div>
                @if($isBusyToday)
                    <p class="text-xs text-red-600 mt-2 flex items-center">
                        <i class="fas fa-calendar-times mr-1"></i>
                        Marked as busy today - not accepting bookings
                    </p>
                @elseif(!$user->servicemanProfile->is_available)
                    <p class="text-xs text-red-600 mt-2 flex items-center">
                        <i class="fas fa-ban mr-1"></i>
                        Currently not accepting new bookings
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    /* Calendar Wrapper Styles */
    .availability-calendar-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .availability-calendar-wrapper .fc {
        min-width: 280px;
    }
    
    /* Mobile Optimizations */
    @media (max-width: 640px) {
        .availability-calendar-wrapper .fc-header-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .availability-calendar-wrapper .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        
        .availability-calendar-wrapper .fc-toolbar-title {
            font-size: 1rem !important;
            margin: 0.5rem 0;
        }
        
        .availability-calendar-wrapper .fc-button {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.875rem !important;
        }
        
        .availability-calendar-wrapper .fc-daygrid-day {
            min-height: 70px !important;
        }
        
        .availability-calendar-wrapper .fc-daygrid-day-number {
            padding: 0.25rem !important;
            font-size: 0.875rem !important;
        }
        
        .availability-calendar-wrapper .fc-col-header-cell {
            padding: 0.5rem 0.25rem !important;
        }
        
        .availability-calendar-wrapper .fc-col-header-cell-cushion {
            font-size: 0.75rem !important;
            font-weight: 600;
        }
    }
    
    /* Desktop Enhancements */
    @media (min-width: 641px) {
        .availability-calendar-wrapper .fc {
            font-size: 1rem;
        }
        
        .availability-calendar-wrapper .fc-daygrid-day {
            min-height: 100px;
        }
        
        .availability-calendar-wrapper .fc-daygrid-day-number {
            font-size: 1rem !important;
            padding: 0.5rem !important;
        }
    }
    
    /* Large screen enhancements */
    @media (min-width: 1024px) {
        .availability-calendar-wrapper .fc {
            font-size: 1.05rem;
        }
        
        .availability-calendar-wrapper .fc-daygrid-day {
            min-height: 110px;
        }
        
        .availability-calendar-wrapper .fc-col-header-cell-cushion {
            font-size: 0.95rem !important;
            padding: 0.75rem !important;
        }
    }
    
    /* Calendar Color Customizations */
    .availability-calendar-wrapper .fc-theme-standard td,
    .availability-calendar-wrapper .fc-theme-standard th {
        border-color: #e5e7eb;
    }
    
    .availability-calendar-wrapper .fc-daygrid-day-frame {
        transition: background-color 0.2s;
    }
    
    .availability-calendar-wrapper .fc-daygrid-day:hover .fc-daygrid-day-frame {
        background-color: #f9fafb;
    }
    
    .availability-calendar-wrapper .fc-day-today {
        background-color: #eff6ff !important;
    }
    
    .availability-calendar-wrapper .fc-day-today .fc-daygrid-day-number {
        color: #2563eb;
        font-weight: 700;
    }
    
    /* Event Styling */
    .availability-calendar-wrapper .fc-event {
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .availability-calendar-wrapper .fc-event-title {
        padding: 0;
    }
    
    /* Button Styling */
    .availability-calendar-wrapper .fc-button-primary {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .availability-calendar-wrapper .fc-button-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    
    .availability-calendar-wrapper .fc-button-primary:disabled {
        background-color: #9ca3af;
        border-color: #9ca3af;
        opacity: 0.6;
    }
    
    .availability-calendar-wrapper .fc-button-active {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }
    
    /* Header Styling */
    .availability-calendar-wrapper .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }
    
    /* Improve readability on small screens */
    @media (max-width: 640px) {
        .availability-calendar-wrapper {
            margin: 0 -0.5rem;
        }
        
        .availability-calendar-wrapper .fc {
            padding: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarContainer = document.getElementById('calendar-container');
    if (calendarContainer) {
        // Detect mobile screen
        const isMobile = window.innerWidth < 640;
        
        const calendar = new FullCalendar.Calendar(calendarContainer, {
            initialView: isMobile ? 'dayGridMonth' : 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            height: isMobile ? 400 : 550,
            aspectRatio: isMobile ? 1.0 : 2.0,
            events: '{{ route("servicemen.calendar", $user->id) }}',
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            dayMaxEvents: true,
            moreLinkClick: 'popover',
            eventDidMount: function(info) {
                // Only busy dates are shown as events (red)
                if (info.event.title === 'Busy') {
                    info.el.style.backgroundColor = '#ef4444';
                    info.el.style.borderColor = '#dc2626';
                    info.el.style.color = 'white';
                    info.el.style.fontWeight = '600';
                    info.el.title = 'Busy - Not available for booking';
                    
                    // Add icon for busy dates
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl && !titleEl.innerHTML.includes('fa-times')) {
                        titleEl.innerHTML = '<i class="fas fa-times mr-1"></i> Busy';
                    }
                }
            },
            eventClick: function(info) {
                // Prevent default action
                info.jsEvent.preventDefault();
            },
            // Improve date cell rendering
            dayCellDidMount: function(info) {
                // Add subtle hover effect
                info.el.style.cursor = 'default';
            },
            // Custom date formatting
            dayHeaderFormat: { weekday: isMobile ? 'short' : 'short' },
            // Responsive week numbers
            weekNumbers: false,
            // Handle window resize
            windowResize: function() {
                const isNowMobile = window.innerWidth < 640;
                if (isNowMobile !== isMobile) {
                    calendar.setOption('aspectRatio', isNowMobile ? 1.0 : 2.0);
                    calendar.setOption('height', isNowMobile ? 400 : 550);
                }
            }
        });
        
        calendar.render();
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                calendar.updateSize();
            }, 250);
        });
    }
});
</script>
@endpush
@endsection

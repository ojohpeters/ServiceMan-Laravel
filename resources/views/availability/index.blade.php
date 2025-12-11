@extends('layouts.app')

@section('title', 'Manage Availability Calendar')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .fc-event-available {
        background-color: #10b981 !important;
        border-color: #059669 !important;
    }
    .fc-event-busy {
        background-color: #ef4444 !important;
        border-color: #dc2626 !important;
    }
    .availability-calendar {
        max-height: 600px;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Availability Calendar</h1>
                    <p class="text-gray-600">Mark your busy days. All unmarked days are automatically available to clients.</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="saveAllChanges()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">How it works:</h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>By default, all days are <strong>available</strong> for clients to book</li>
                        <li>Click on a date to mark it as <strong>busy/unavailable</strong></li>
                        <li>Click again on a busy date to mark it back as <strong>available</strong></li>
                        <li>Clients will see your availability calendar on your profile page</li>
                        <li>Green dates = Available, Red dates = Busy</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
            <div id="availability-calendar" class="availability-calendar"></div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button onclick="markNextWeekBusy()" class="p-4 bg-red-50 border-2 border-red-200 rounded-lg hover:bg-red-100 transition-colors text-left">
                    <i class="fas fa-ban text-red-600 text-xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900">Mark Next Week Busy</h3>
                    <p class="text-sm text-gray-600 mt-1">Block all days in the next 7 days</p>
                </button>
                
                <button onclick="clearAllBusy()" class="p-4 bg-green-50 border-2 border-green-200 rounded-lg hover:bg-green-100 transition-colors text-left">
                    <i class="fas fa-check-circle text-green-600 text-xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900">Clear All Busy Days</h3>
                    <p class="text-sm text-gray-600 mt-1">Make all days available</p>
                </button>
                
                <button onclick="markWeekendsBusy()" class="p-4 bg-yellow-50 border-2 border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors text-left">
                    <i class="fas fa-calendar-week text-yellow-600 text-xl mb-2"></i>
                    <h3 class="font-semibold text-gray-900">Block Weekends</h3>
                    <p class="text-sm text-gray-600 mt-1">Mark all Saturdays & Sundays as busy</p>
                </button>
            </div>
        </div>

        <!-- Pending Changes Indicator -->
        <div id="pendingChanges" class="hidden fixed bottom-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-circle"></i>
                <span class="font-semibold">You have unsaved changes</span>
                <button onclick="saveAllChanges()" class="bg-white text-blue-600 px-4 py-1 rounded font-semibold hover:bg-gray-100">
                    Save Now
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
let calendar;
let busyDates = new Set();
let hasUnsavedChanges = false;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    const calendarEl = document.getElementById('availability-calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            // Fetch existing availability
            fetch('{{ route("availability.index") }}?format=json')
                .then(response => response.json())
                .then(data => {
                    const events = [];
                    busyDates.clear();
                    
                    // Add busy dates
                    data.busy_dates.forEach(date => {
                        busyDates.add(date);
                        events.push({
                            title: 'Busy',
                            start: date,
                            allDay: true,
                            backgroundColor: '#ef4444',
                            borderColor: '#dc2626',
                            classNames: ['fc-event-busy'],
                            extendedProps: { type: 'busy' }
                        });
                    });
                    
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error loading availability:', error);
                    failureCallback(error);
                });
        },
        dateClick: function(info) {
            const dateStr = info.dateStr;
            const isBusy = busyDates.has(dateStr);
            
            if (isBusy) {
                // Remove from busy dates
                busyDates.delete(dateStr);
                calendar.getEventById('busy-' + dateStr)?.remove();
            } else {
                // Add to busy dates
                busyDates.add(dateStr);
                calendar.addEvent({
                    id: 'busy-' + dateStr,
                    title: 'Busy',
                    start: dateStr,
                    allDay: true,
                    backgroundColor: '#ef4444',
                    borderColor: '#dc2626',
                    classNames: ['fc-event-busy'],
                    extendedProps: { type: 'busy' }
                });
            }
            
            hasUnsavedChanges = true;
            updatePendingChangesIndicator();
        },
        eventClick: function(info) {
            // Allow removing busy dates by clicking on them
            const dateStr = info.event.startStr;
            busyDates.delete(dateStr);
            info.event.remove();
            
            hasUnsavedChanges = true;
            updatePendingChangesIndicator();
        }
    });
    
    calendar.render();
    
    // Load existing busy dates
    loadBusyDates();
});

function updatePendingChangesIndicator() {
    const indicator = document.getElementById('pendingChanges');
    if (hasUnsavedChanges) {
        indicator.classList.remove('hidden');
    } else {
        indicator.classList.add('hidden');
    }
}

function loadBusyDates() {
    fetch('{{ route("availability.index") }}?format=json')
        .then(response => response.json())
        .then(data => {
            busyDates.clear();
            data.busy_dates.forEach(date => {
                busyDates.add(date);
            });
        })
        .catch(error => {
            console.error('Error loading busy dates:', error);
        });
}

async function saveAllChanges() {
    const busyDatesArray = Array.from(busyDates);
    
    try {
        const response = await fetch('{{ route("availability.bulk-update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                busy_dates: busyDatesArray,
                action: 'update_busy_dates'
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            hasUnsavedChanges = false;
            updatePendingChangesIndicator();
            
            // Show success message
            showNotification('Availability calendar updated successfully!', 'success');
            
            // Refresh calendar
            calendar.refetchEvents();
        } else {
            throw new Error(data.message || 'Failed to save changes');
        }
    } catch (error) {
        console.error('Error saving availability:', error);
        showNotification('Failed to save changes. Please try again.', 'error');
    }
}

function markNextWeekBusy() {
    const today = new Date();
    for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        const dateStr = date.toISOString().split('T')[0];
        
        if (!busyDates.has(dateStr)) {
            busyDates.add(dateStr);
            calendar.addEvent({
                id: 'busy-' + dateStr,
                title: 'Busy',
                start: dateStr,
                allDay: true,
                backgroundColor: '#ef4444',
                borderColor: '#dc2626',
                classNames: ['fc-event-busy']
            });
        }
    }
    
    hasUnsavedChanges = true;
    updatePendingChangesIndicator();
    showNotification('Next 7 days marked as busy. Don\'t forget to save!', 'info');
}

function markWeekendsBusy() {
    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth();
    
    // Get all dates in current and next month
    for (let month = 0; month < 2; month++) {
        const date = new Date(currentYear, currentMonth + month, 1);
        const daysInMonth = new Date(currentYear, currentMonth + month + 1, 0).getDate();
        
        for (let day = 1; day <= daysInMonth; day++) {
            const checkDate = new Date(currentYear, currentMonth + month, day);
            const dayOfWeek = checkDate.getDay();
            
            // Saturday = 6, Sunday = 0
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                const dateStr = checkDate.toISOString().split('T')[0];
                
                if (!busyDates.has(dateStr)) {
                    busyDates.add(dateStr);
                    calendar.addEvent({
                        id: 'busy-' + dateStr,
                        title: 'Busy',
                        start: dateStr,
                        allDay: true,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        classNames: ['fc-event-busy']
                    });
                }
            }
        }
    }
    
    hasUnsavedChanges = true;
    updatePendingChangesIndicator();
    showNotification('Weekends marked as busy. Don\'t forget to save!', 'info');
}

function clearAllBusy() {
    if (!confirm('Are you sure you want to clear all busy days? This will make all days available.')) {
        return;
    }
    
    // Remove all busy events
    calendar.getEvents().forEach(event => {
        if (event.extendedProps?.type === 'busy') {
            event.remove();
        }
    });
    
    busyDates.clear();
    hasUnsavedChanges = true;
    updatePendingChangesIndicator();
    showNotification('All busy days cleared. Don\'t forget to save!', 'info');
}

function showNotification(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Warn before leaving if there are unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>
@endpush
@endsection

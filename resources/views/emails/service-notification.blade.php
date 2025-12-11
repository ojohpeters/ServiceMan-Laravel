@component('mail::message')
# {{ $title }}

Hello {{ $user->first_name ?? $user->username }},

{{ $message }}

@if($serviceRequest)
## Service Request Details

**Request ID:** #{{ $serviceRequest->id }}  
**Service Type:** {{ $serviceRequest->category->name ?? 'N/A' }}  
**Status:** {{ $serviceRequest->status }}  
**Booking Date:** {{ $serviceRequest->booking_date ? $serviceRequest->booking_date->format('F j, Y') : 'N/A' }}

@if($serviceRequest->location || $serviceRequest->client_address)
**Location:** {{ $serviceRequest->location ?? $serviceRequest->client_address }}
@endif

@component('mail::button', ['url' => route('service-requests.show', $serviceRequest->id)])
View Service Request
@endcomponent
@endif

@if(isset($extraData['action_url']) && isset($extraData['action_text']))
@component('mail::button', ['url' => $extraData['action_url'], 'color' => 'success'])
{{ $extraData['action_text'] }}
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }} Team

@component('mail::subcopy')
If you're having trouble clicking the button, copy and paste the URL below into your web browser:
@if($serviceRequest)
{{ route('service-requests.show', $serviceRequest->id) }}
@endif
@endcomponent
@endcomponent


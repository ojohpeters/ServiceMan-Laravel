<?php

namespace App\Http\Controllers;

use App\Models\ServicemanAvailability;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            abort(403);
        }

        // If JSON format requested (for calendar API)
        if ($request->get('format') === 'json') {
            $busyDates = ServicemanAvailability::where('serviceman_id', $user->id)
                ->where('is_available', false)
                ->where('date', '>=', now()->startOfMonth())
                ->where('date', '<=', now()->addMonths(3)->endOfMonth())
                ->pluck('date')
                ->map(function($date) {
                    return $date->format('Y-m-d');
                })
                ->toArray();
            
            return response()->json([
                'busy_dates' => $busyDates
            ]);
        }

        $availabilities = ServicemanAvailability::where('serviceman_id', $user->id)
            ->upcoming()
            ->orderBy('date')
            ->get();

        return view('availability.index', compact('availabilities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        ServicemanAvailability::updateOrCreate(
            [
                'serviceman_id' => $user->id,
                'date' => $request->date,
            ],
            [
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_available' => $request->boolean('is_available', true),
                'notes' => $request->notes,
            ]
        );

        return back()->with('success', 'Availability updated successfully!');
    }

    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $availabilities = [];
        foreach ($request->dates as $date) {
            $availabilities[] = ServicemanAvailability::updateOrCreate(
                [
                    'serviceman_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'is_available' => $request->boolean('is_available', true),
                ]
            );
        }

        return response()->json(['message' => 'Availability updated successfully', 'data' => $availabilities]);
    }

    public function destroy($id)
    {
        $availability = ServicemanAvailability::findOrFail($id);
        
        if ($availability->serviceman_id !== Auth::id()) {
            abort(403);
        }

        $availability->delete();

        return back()->with('success', 'Availability removed successfully!');
    }

    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'busy_dates' => 'required|array',
            'busy_dates.*' => 'required|date|after_or_equal:today',
            'action' => 'required|in:update_busy_dates',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Get date range
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonths(3)->endOfMonth();
        
        // Get all existing availabilities in range
        $existingAvailabilities = ServicemanAvailability::where('serviceman_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        $busyDates = collect($request->busy_dates)->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        });

        // Update or create busy dates
        foreach ($busyDates as $date) {
            $carbonDate = \Carbon\Carbon::parse($date);
            if ($carbonDate->isFuture() || $carbonDate->isToday()) {
                ServicemanAvailability::updateOrCreate(
                    [
                        'serviceman_id' => $user->id,
                        'date' => $carbonDate,
                    ],
                    [
                        'is_available' => false,
                        'start_time' => null,
                        'end_time' => null,
                        'notes' => 'Marked as busy',
                    ]
                );
            }
        }

        // Remove busy flags from dates that are no longer busy (but only if they exist)
        // Don't delete - just mark as available if they were explicitly set
        $existingAvailabilities->each(function($availability) use ($busyDates) {
            $dateStr = $availability->date->format('Y-m-d');
            if (!$busyDates->contains($dateStr) && !$availability->is_available) {
                // Only update if it's marked as busy - don't create new records
                $availability->update(['is_available' => true]);
            }
        });

        return response()->json([
            'message' => 'Availability updated successfully',
            'busy_dates' => $busyDates->toArray()
        ]);
    }

    public function checkAvailability(Request $request, $servicemanId)
    {
        $serviceman = User::findOrFail($servicemanId);
        
        if (!$serviceman->isServiceman()) {
            return response()->json(['error' => 'User is not a serviceman'], 400);
        }

        $date = $request->get('date');
        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $checkDate = \Carbon\Carbon::parse($date);
        $isBusy = $serviceman->isBusyOnDate($checkDate);
        
        return response()->json([
            'is_busy' => $isBusy,
            'is_available' => !$isBusy && $serviceman->servicemanProfile->is_available,
            'date' => $checkDate->format('Y-m-d'),
            'date_formatted' => $checkDate->format('l, F j, Y'),
        ]);
    }

    public function getCalendar($servicemanId)
    {
        $serviceman = User::findOrFail($servicemanId);
        
        if (!$serviceman->isServiceman()) {
            abort(404);
        }

        $startDate = Carbon::parse(request('start', Carbon::today()));
        $endDate = Carbon::parse(request('end', $startDate->copy()->addMonths(2)));

        // Get all busy dates in range
        // All other dates are implicitly available (no event = available)
        $busyDates = ServicemanAvailability::where('serviceman_id', $servicemanId)
            ->forDateRange($startDate, $endDate)
            ->where('is_available', false)
            ->pluck('date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();

        // Return events - only show busy dates (red)
        // All dates without events are available by default
        $events = array_map(function($date) {
            return [
                'title' => 'Busy',
                'start' => $date,
                'allDay' => true,
                'backgroundColor' => '#ef4444',
                'borderColor' => '#dc2626',
            ];
        }, $busyDates);
        
        // Note: Available dates are shown by default (no event = available)
        // You could add green events for explicitly available dates if needed
        
        return response()->json($events);
    }
}

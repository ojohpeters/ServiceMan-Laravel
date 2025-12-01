<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomServiceRequest;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomServiceRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            return redirect()->route('dashboard')->with('error', 'Only servicemen can request custom services.');
        }

        $customRequests = CustomServiceRequest::where('serviceman_id', $user->id)
            ->with(['category', 'reviewer'])
            ->latest()
            ->paginate(10);

        return view('custom-services.index', compact('customRequests'));
    }

    public function create()
    {
        if (!Auth::user()->isServiceman()) {
            return redirect()->route('dashboard')->with('error', 'Only servicemen can request custom services.');
        }

        return view('custom-services.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            return back()->with('error', 'Only servicemen can request custom services.');
        }

        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'service_description' => 'required|string|max:1000',
            'why_needed' => 'nullable|string|max:500',
            'target_market' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customRequest = CustomServiceRequest::create([
            'serviceman_id' => $user->id,
            'service_name' => $request->service_name,
            'service_description' => $request->service_description,
            'why_needed' => $request->why_needed,
            'target_market' => $request->target_market,
            'status' => 'PENDING',
        ]);

        // Notify admin
        AppNotification::create([
            'user_id' => null, // Admin notification
            'service_request_id' => null,
            'type' => 'CUSTOM_SERVICE_REQUEST',
            'title' => '🆕 New Custom Service Request',
            'message' => "Serviceman {$user->full_name} has requested a new service category: '{$request->service_name}'. Please review and respond.",
            'is_read' => false,
        ]);

        // Notify serviceman
        AppNotification::create([
            'user_id' => $user->id,
            'service_request_id' => null,
            'type' => 'CUSTOM_SERVICE_SUBMITTED',
            'title' => '✅ Custom Service Request Submitted',
            'message' => "Your request for '{$request->service_name}' has been submitted. Admin will review it and notify you when it's available or provide feedback.",
            'is_read' => false,
        ]);

        return redirect()->route('custom-services.index')
            ->with('success', 'Custom service request submitted successfully! Admin will review it shortly.');
    }
}

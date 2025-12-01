<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $query = Rating::with(['serviceRequest', 'client', 'serviceman']);
        
        // Filter by serviceman if provided
        if ($request->has('serviceman_id')) {
            $query->where('serviceman_id', $request->serviceman_id);
        }
        
        // Filter by rating if provided
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        $ratings = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json($ratings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);
        $user = Auth::user();

        // Check if user is the client of this service request
        if (!$user->isClient() || $serviceRequest->client_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if service request is completed
        if ($serviceRequest->status !== 'COMPLETED') {
            return response()->json(['error' => 'Service request must be completed before rating'], 400);
        }

        // Check if rating already exists
        if ($serviceRequest->rating) {
            return response()->json(['error' => 'Rating already exists for this service request'], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create rating
            $rating = Rating::create([
                'service_request_id' => $serviceRequest->id,
                'client_id' => $user->id,
                'serviceman_id' => $serviceRequest->serviceman_id,
                'rating' => $request->rating,
                'review' => $request->review
            ]);

            // Update serviceman profile rating
            $servicemanProfile = $serviceRequest->serviceman->servicemanProfile;
            $servicemanProfile->updateRating($request->rating);

            DB::commit();

            $rating->load(['serviceRequest', 'client', 'serviceman']);

            return response()->json($rating, 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to create rating'], 500);
        }
    }

    public function show($id)
    {
        $rating = Rating::with(['serviceRequest', 'client', 'serviceman'])->findOrFail($id);
        
        return response()->json($rating);
    }

    public function update(Request $request, $id)
    {
        $rating = Rating::findOrFail($id);
        $user = Auth::user();

        // Check if user is the client who created this rating
        if ($rating->client_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'review' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $oldRating = $rating->rating;
        
        $rating->update($request->only(['rating', 'review']));

        // Update serviceman profile if rating changed
        if ($request->has('rating') && $request->rating !== $oldRating) {
            $servicemanProfile = $rating->serviceman->servicemanProfile;
            
            // Recalculate average rating
            $allRatings = Rating::where('serviceman_id', $rating->serviceman_id)->get();
            $averageRating = $allRatings->avg('rating');
            
            $servicemanProfile->update([
                'rating' => round($averageRating, 2)
            ]);
        }

        $rating->load(['serviceRequest', 'client', 'serviceman']);

        return response()->json($rating);
    }

    public function destroy($id)
    {
        $rating = Rating::findOrFail($id);
        $user = Auth::user();

        // Check if user is the client who created this rating or admin
        if ($rating->client_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $servicemanId = $rating->serviceman_id;
        
        DB::beginTransaction();
        
        try {
            $rating->delete();
            
            // Recalculate serviceman average rating
            $servicemanProfile = User::find($servicemanId)->servicemanProfile;
            $remainingRatings = Rating::where('serviceman_id', $servicemanId)->get();
            
            if ($remainingRatings->count() > 0) {
                $averageRating = $remainingRatings->avg('rating');
                $servicemanProfile->update([
                    'rating' => round($averageRating, 2),
                    'total_jobs_completed' => max(0, $servicemanProfile->total_jobs_completed - 1)
                ]);
            } else {
                $servicemanProfile->update([
                    'rating' => 0.0,
                    'total_jobs_completed' => max(0, $servicemanProfile->total_jobs_completed - 1)
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Rating deleted successfully']);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to delete rating'], 500);
        }
    }

    public function getServicemanRatings($servicemanId)
    {
        $serviceman = User::findOrFail($servicemanId);
        
        if (!$serviceman->isServiceman()) {
            return response()->json(['error' => 'User is not a serviceman'], 404);
        }

        $ratings = Rating::where('serviceman_id', $servicemanId)
            ->with(['serviceRequest', 'client'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($ratings);
    }
}
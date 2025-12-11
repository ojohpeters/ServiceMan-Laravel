<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount('servicemen')
            ->take(6)
            ->get();
        
        // Get featured servicemen sorted by rating (highest to lowest)
        // Use servicemanProfile->rating which is the calculated average rating
        $featuredServicemen = User::where('user_type', 'SERVICEMAN')
            ->with(['servicemanProfile.category', 'ratingsReceived'])
            ->whereHas('servicemanProfile', function($query) {
                $query->where('is_available', true);
            })
            ->get()
            ->map(function($serviceman) {
                // Calculate average rating from actual ratings if profile rating is not set
                $profileRating = $serviceman->servicemanProfile->rating ?? 0;
                if ($profileRating == 0 && $serviceman->ratingsReceived->count() > 0) {
                    $calculatedRating = $serviceman->ratingsReceived->avg('rating');
                    $serviceman->servicemanProfile->rating = $calculatedRating ?? 0;
                }
                return $serviceman;
            })
            ->sortByDesc(function($serviceman) {
                return $serviceman->servicemanProfile->rating ?? 0;
            })
            ->values() // Reset array keys after sorting
            ->take(8);

        // Get real testimonials (only featured ones approved by admin)
        $testimonials = \App\Models\Rating::with(['client', 'serviceman', 'serviceRequest.category'])
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Get real statistics
        $stats = [
            'total_clients' => User::where('user_type', 'CLIENT')->count(),
            'total_servicemen' => User::where('user_type', 'SERVICEMAN')
                ->whereHas('servicemanProfile', function($query) {
                    $query->where('is_available', true);
                })
                ->count(),
            'total_services_completed' => \App\Models\ServiceRequest::where('status', 'COMPLETED')->count(),
            'total_categories' => Category::where('is_active', true)->count(),
        ];

        return view('home', compact('categories', 'featuredServicemen', 'stats', 'testimonials'));
    }

    public function about()
    {
        return view('about');
    }

    public function faq()
    {
        return view('faq');
    }

    public function contact(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:5000',
            ]);

            // Here you can add email sending logic
            // For now, just redirect with success message
            return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');
        }

        return view('contact');
    }
}


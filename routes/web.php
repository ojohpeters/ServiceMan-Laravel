<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ServiceRequestController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PaystackPaymentController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contact'])->name('contact.submit');
Route::get('/services', [CategoryController::class, 'index'])->name('services');
Route::get('/services/{category}', [CategoryController::class, 'show'])->name('services.category');
Route::get('/servicemen/{user}', [ProfileController::class, 'showPublic'])->name('servicemen.show');
Route::get('/servicemen/{serviceman}/calendar', [\App\Http\Controllers\AvailabilityController::class, 'getCalendar'])->name('servicemen.calendar');


// API routes for AJAX
Route::get('/api/categories/{category}/servicemen', [CategoryController::class, 'getServicemen'])->name('api.categories.servicemen');
Route::get('/api/skills/common', [\App\Http\Controllers\Api\SkillsController::class, 'getCommonSkills'])->name('api.skills.common');
Route::get('/api/servicemen/{servicemanId}/check-availability', [\App\Http\Controllers\AvailabilityController::class, 'checkAvailability'])->name('api.servicemen.check-availability');

// Email verification route (can be accessed by anyone)
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Resend verification email (authenticated users only)
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend')->middleware('auth');

// Pending verification page for unapproved servicemen
Route::get('/pending-verification', [AuthController::class, 'showPendingVerification'])->name('pending-verification')->middleware('auth');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard (requires email verification - handled by global middleware)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/client', [ProfileController::class, 'clientProfile'])->name('profile.client');
    Route::put('/profile/client', [ProfileController::class, 'updateClientProfile'])->name('profile.client.update');
    Route::get('/profile/serviceman', [ProfileController::class, 'servicemanProfile'])->name('profile.serviceman');
    Route::put('/profile/serviceman', [ProfileController::class, 'updateServicemanProfile'])->name('profile.serviceman.update');
    
        // Service requests (require email verification)
        Route::middleware('verified')->group(function () {
            Route::get('/service-requests', [ServiceRequestController::class, 'index'])->name('service-requests.index');
            Route::get('/service-requests/create', [ServiceRequestController::class, 'create'])->name('service-requests.create');
            Route::post('/service-requests', [ServiceRequestController::class, 'store'])->name('service-requests.store');
            Route::get('/service-requests/pay-booking-fee', [ServiceRequestController::class, 'showPaymentPage'])->name('service-requests.pay-booking-fee');
      Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
      Route::get('/service-requests/{serviceRequest}/edit', [ServiceRequestController::class, 'edit'])->name('service-requests.edit');
      Route::put('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'update'])->name('service-requests.update');
      Route::post('/service-requests/{serviceRequest}/accept', [ServiceRequestController::class, 'acceptAssignment'])->name('service-requests.accept');
      Route::post('/service-requests/{serviceRequest}/decline', [ServiceRequestController::class, 'declineAssignment'])->name('service-requests.decline');
      Route::post('/service-requests/{serviceRequest}/submit-estimate', [ServiceRequestController::class, 'submitEstimate'])->name('service-requests.submit-estimate');
      Route::post('/service-requests/{serviceRequest}/mark-complete', [ServiceRequestController::class, 'markComplete'])->name('service-requests.mark-complete');
      Route::post('/service-requests/{serviceRequest}/accept-cost', [ServiceRequestController::class, 'acceptCost'])->name('service-requests.accept-cost');
      Route::post('/service-requests/{serviceRequest}/negotiate', [ServiceRequestController::class, 'createNegotiation'])->name('service-requests.negotiate');
      Route::post('/service-requests/{serviceRequest}/rate', [ServiceRequestController::class, 'submitRating'])->name('service-requests.rate');
        });  // End of verified middleware group
    
    // Custom Service Requests (Servicemen only, requires email verification)
    Route::middleware('verified')->group(function () {
        Route::get('/custom-services', [\App\Http\Controllers\Web\CustomServiceRequestController::class, 'index'])->name('custom-services.index');
        Route::get('/custom-services/create', [\App\Http\Controllers\Web\CustomServiceRequestController::class, 'create'])->name('custom-services.create');
        Route::post('/custom-services', [\App\Http\Controllers\Web\CustomServiceRequestController::class, 'store'])->name('custom-services.store');
    });
    
    // Payments
    Route::get('/payments', [PaystackPaymentController::class, 'index'])->name('payments.index');
    Route::post('/paystack/initialize', [PaystackPaymentController::class, 'initialize'])->name('paystack.initialize');
    Route::get('/payments/verify', [PaystackPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/webhook', [PaystackPaymentController::class, 'webhook'])->name('payments.webhook');
    Route::get('/payments/history', [PaystackPaymentController::class, 'history'])->name('payments.history');
    
    // Notifications
    Route::get('/notifications', function() {
        try {
            if (auth()->user()->isAdmin()) {
                // Admin sees their own notifications AND system-wide admin notifications (user_id = null)
                $notifications = \App\Models\AppNotification::where(function($query) {
                    $query->where('user_id', auth()->id())
                          ->orWhereNull('user_id');
                })
                ->with('serviceRequest')
                ->latest()
                ->paginate(20);
            } else {
                // Regular users ONLY see their own notifications
                $notifications = \App\Models\AppNotification::where('user_id', auth()->id())
                    ->with('serviceRequest')
                    ->latest()
                    ->paginate(20);
            }
            return view('notifications.index', compact('notifications'));
        } catch (\Exception $e) {
            \Log::error('Notifications page error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load notifications. Please try again.');
        }
    })->name('notifications.index');
    
    // Mark single notification as read
    Route::post('/notifications/{notification}/mark-read', function(\App\Models\AppNotification $notification) {
        // Verify user has access to this notification
        if ($notification->user_id === auth()->id() || (auth()->user()->isAdmin() && $notification->user_id === null)) {
            $notification->update(['is_read' => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    })->name('notifications.mark-read');
    
    // Mark all notifications as read
    Route::post('/notifications/mark-all-read', function() {
        if (auth()->user()->isAdmin()) {
            \App\Models\AppNotification::where(function($query) {
                $query->where('user_id', auth()->id())
                      ->orWhereNull('user_id');
            })->update(['is_read' => true]);
        } else {
            \App\Models\AppNotification::where('user_id', auth()->id())
                ->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    })->name('notifications.mark-all-read');
    
    // Get unread notification count
    Route::get('/api/notifications/count', function() {
        if (auth()->user()->isAdmin()) {
            $count = \App\Models\AppNotification::where(function($query) {
                $query->where('user_id', auth()->id())
                      ->orWhereNull('user_id');
            })->unread()->count();
        } else {
            $count = \App\Models\AppNotification::where('user_id', auth()->id())
                ->unread()
                ->count();
        }
        return response()->json(['count' => $count]);
    })->name('notifications.count');

    // Test route for debugging
    Route::get('/test-servicemen', function() {
        $servicemen = \App\Models\User::where('user_type', 'SERVICEMAN')
            ->with(['servicemanProfile.category'])
            ->get();
        
        $result = [];
        foreach ($servicemen as $serviceman) {
            $result[] = [
                'id' => $serviceman->id,
                'username' => $serviceman->username,
                'first_name' => $serviceman->first_name,
                'last_name' => $serviceman->last_name,
                'full_name' => $serviceman->getFullNameAttribute(),
                'category' => $serviceman->servicemanProfile->category->name ?? 'No category',
                'rating' => $serviceman->servicemanProfile->rating ?? 0,
                'total_jobs' => $serviceman->servicemanProfile->total_jobs_completed ?? 0,
                'bio' => $serviceman->servicemanProfile->bio ?? 'No bio'
            ];
        }
        
        return response()->json($result);
    });

    // Test specific category endpoint
    Route::get('/test-category/{id}', function($id) {
        $category = \App\Models\Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        
        $controller = new \App\Http\Controllers\Web\CategoryController();
        return $controller->getServicemen($category);
    });
    
    // Availability routes (for servicemen)
    Route::middleware('verified')->group(function () {
        Route::get('/availability', [\App\Http\Controllers\AvailabilityController::class, 'index'])->name('availability.index');
        Route::post('/availability', [\App\Http\Controllers\AvailabilityController::class, 'store'])->name('availability.store');
        Route::post('/availability/bulk-update', [\App\Http\Controllers\AvailabilityController::class, 'bulkUpdate'])->name('availability.bulk-update');
        Route::delete('/availability/{id}', [\App\Http\Controllers\AvailabilityController::class, 'destroy'])->name('availability.destroy');
    });
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::patch('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.patch');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/service-requests', [AdminController::class, 'serviceRequests'])->name('service-requests');
        Route::post('/service-requests/{serviceRequest}/assign-serviceman', [AdminController::class, 'assignServiceman'])->name('service-requests.assign-serviceman');
        Route::post('/service-requests/{serviceRequest}/change-serviceman', [AdminController::class, 'changeServiceman'])->name('service-requests.change-serviceman');
        Route::post('/service-requests/{serviceRequest}/submit-cost-estimate', [AdminController::class, 'submitCostEstimate'])->name('service-requests.submit-cost-estimate');
        Route::post('/service-requests/{serviceRequest}/approve-cost-estimate', [AdminController::class, 'approveCostEstimate'])->name('service-requests.approve-cost-estimate');
        Route::post('/service-requests/{serviceRequest}/mark-work-completed', [AdminController::class, 'markWorkCompleted'])->name('service-requests.mark-work-completed');
        Route::get('/category-requests', [AdminController::class, 'categoryRequests'])->name('category-requests');
        Route::post('/category-requests/{categoryRequest}/handle', [AdminController::class, 'handleCategoryRequest'])->name('category-requests.handle');
        Route::post('/service-requests/{serviceRequest}/assign-backup-serviceman', [AdminController::class, 'assignBackupServiceman'])->name('service-requests.assign-backup-serviceman');
        Route::post('/service-requests/{serviceRequest}/set-final-cost', [AdminController::class, 'setFinalCost'])->name('service-requests.set-final-cost');
        Route::post('/negotiations/{negotiation}/handle', [AdminController::class, 'handleNegotiation'])->name('negotiations.handle');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/servicemen', [AdminController::class, 'servicemen'])->name('servicemen');
        Route::get('/queue-status', function() {
            if (!auth()->user()->isAdmin()) abort(403);
            
            return response()->json([
                'pending_jobs' => \DB::table('jobs')->count(),
                'failed_jobs' => \DB::table('failed_jobs')->count(),
                'last_processed' => \Cache::get('queue_last_processed', 'Never'),
                'status' => 'ok'
            ]);
        })->name('admin.queue-status');
        Route::post('/users/create-admin', [AdminController::class, 'createAdmin'])->name('users.create-admin');
        Route::get('/pending-servicemen', [AdminController::class, 'pendingServicemen'])->name('pending-servicemen');
        Route::post('/servicemen/{user}/assign-category', [AdminController::class, 'assignCategory'])->name('servicemen.assign-category');
        Route::get('/custom-service-requests', [AdminController::class, 'customServiceRequests'])->name('custom-service-requests');
        Route::post('/custom-service-requests/{customServiceRequest}/handle', [AdminController::class, 'handleCustomServiceRequest'])->name('custom-service-requests.handle');
        Route::post('/service-requests/{serviceRequest}/notify-start', [AdminController::class, 'notifyServicemanToStart'])->name('service-requests.notify-start');
        Route::post('/service-requests/{serviceRequest}/confirm-completion', [AdminController::class, 'confirmCompletion'])->name('service-requests.confirm-completion');
        
        // Serviceman Approval Routes
        Route::get('/servicemen-approval', [AdminController::class, 'pendingServicemenApproval'])->name('servicemen-approval');
        Route::post('/servicemen/{user}/approve', [AdminController::class, 'approveServiceman'])->name('servicemen.approve');
        Route::post('/servicemen/{user}/reject', [AdminController::class, 'rejectServiceman'])->name('servicemen.reject');
        Route::post('/servicemen/{user}/revoke-approval', [AdminController::class, 'revokeApproval'])->name('servicemen.revoke-approval');
        
        // Testimonials Management Routes
        Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('testimonials');
        Route::post('/testimonials/{rating}/toggle-featured', [AdminController::class, 'toggleTestimonialFeatured'])->name('testimonials.toggle-featured');
        
        // Log Viewer Routes
        Route::get('/logs', [\App\Http\Controllers\Web\LogController::class, 'index'])->name('logs');
        Route::delete('/logs', [\App\Http\Controllers\Web\LogController::class, 'clear'])->name('logs.clear');
        Route::get('/logs/download', [\App\Http\Controllers\Web\LogController::class, 'download'])->name('logs.download');
    });
});
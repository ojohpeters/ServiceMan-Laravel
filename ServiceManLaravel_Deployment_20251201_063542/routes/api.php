<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NegotiationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes with rate limiting
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
});

Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

// Payment webhook (no auth required)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

// Public category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/servicemen', [CategoryController::class, 'getServicemenByCategory']);

// Public serviceman profile routes
Route::get('/servicemen/{userId}', [UserController::class, 'getPublicServicemanProfile']);

// Public rating routes
Route::get('/ratings', [RatingController::class, 'index']);
Route::get('/ratings/servicemen/{servicemanId}', [RatingController::class, 'getServicemanRatings']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    
    // Auth routes
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // User routes
    Route::get('/users/me', [UserController::class, 'getCurrentUser']);
    Route::get('/users/client-profile', [UserController::class, 'getClientProfile']);
    Route::put('/users/client-profile', [UserController::class, 'updateClientProfile']);
    Route::get('/users/serviceman-profile', [UserController::class, 'getServicemanProfile']);
    Route::put('/users/serviceman-profile', [UserController::class, 'updateServicemanProfile']);
    
    // Service request routes
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
    Route::put('/service-requests/{id}', [ServiceRequestController::class, 'update']);
    Route::post('/service-requests/{id}/submit-estimate', [ServiceRequestController::class, 'submitEstimate']);
    Route::post('/service-requests/{id}/mark-complete', [ServiceRequestController::class, 'markComplete']);
    
    // Payment routes
    Route::post('/payments/initialize', [PaymentController::class, 'initialize']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'getPaymentHistory']);
    
    // Negotiation routes
    Route::get('/negotiations', [NegotiationController::class, 'index']);
    Route::post('/negotiations', [NegotiationController::class, 'store']);
    Route::get('/negotiations/{id}', [NegotiationController::class, 'show']);
    Route::post('/negotiations/{id}/accept', [NegotiationController::class, 'accept']);
    Route::post('/negotiations/{id}/reject', [NegotiationController::class, 'reject']);
    Route::post('/negotiations/{id}/counter', [NegotiationController::class, 'counter']);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Rating routes
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/{id}', [RatingController::class, 'show']);
    Route::put('/ratings/{id}', [RatingController::class, 'update']);
    Route::delete('/ratings/{id}', [RatingController::class, 'destroy']);
    
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        
        Route::get('/admin/pending-assignments', [AdminController::class, 'getPendingAssignments']);
        Route::post('/admin/service-requests/{id}/assign-serviceman', [AdminController::class, 'assignServiceman']);
        Route::get('/admin/pricing-review', [AdminController::class, 'getPricingReview']);
        Route::put('/admin/service-requests/{id}/final-cost', [AdminController::class, 'updateFinalCost']);
        
        Route::get('/admin/analytics/revenue', [AdminController::class, 'getRevenueAnalytics']);
        Route::get('/admin/analytics/servicemen', [AdminController::class, 'getTopServicemen']);
        Route::get('/admin/analytics/categories', [AdminController::class, 'getTopCategories']);
        Route::get('/admin/analytics/service-requests', [AdminController::class, 'getServiceRequestStats']);
        Route::get('/admin/analytics/users', [AdminController::class, 'getUserStats']);
        Route::get('/admin/analytics/recent-activity', [AdminController::class, 'getRecentActivity']);
    });
    
    // Development routes (remove in production)
    Route::post('/dev/create-test-servicemen', [UserController::class, 'createTestServicemen']);
});

/*
|--------------------------------------------------------------------------
| Legacy API Routes (for compatibility with Django API)
|--------------------------------------------------------------------------
*/

// Legacy user routes
Route::prefix('users')->middleware('auth:api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/token', [AuthController::class, 'login']);
    Route::post('/token/refresh', [AuthController::class, 'refresh']);
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/password-reset', [AuthController::class, 'forgotPassword']);
    Route::post('/password-reset-confirm', [AuthController::class, 'resetPassword']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/client-profile', [UserController::class, 'getClientProfile']);
    Route::put('/client-profile', [UserController::class, 'updateClientProfile']);
    Route::get('/serviceman-profile', [UserController::class, 'getServicemanProfile']);
    Route::put('/serviceman-profile', [UserController::class, 'updateServicemanProfile']);
    Route::get('/servicemen/{userId}', [UserController::class, 'getPublicServicemanProfile']);
});

// Legacy service routes
Route::prefix('services')->middleware('auth:api')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('admin');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('admin');
    Route::get('/categories/{id}/servicemen', [CategoryController::class, 'getServicemenByCategory']);
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
    Route::put('/service-requests/{id}', [ServiceRequestController::class, 'update']);
});

// Legacy payment routes
Route::prefix('payments')->middleware('auth:api')->group(function () {
    Route::post('/initialize', [PaymentController::class, 'initialize']);
    Route::get('/verify', [PaymentController::class, 'verify']);
});

// Legacy negotiation routes
Route::prefix('negotiations')->middleware('auth:api')->group(function () {
    Route::get('/', [NegotiationController::class, 'index']);
    Route::post('/create', [NegotiationController::class, 'store']);
    Route::post('/{id}/accept', [NegotiationController::class, 'accept']);
    Route::post('/{id}/counter', [NegotiationController::class, 'counter']);
});

// Legacy notification routes
Route::prefix('notifications')->middleware('auth:api')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});

// Legacy rating routes
Route::prefix('ratings')->middleware('auth:api')->group(function () {
    Route::get('/', [RatingController::class, 'index']);
    Route::post('/create', [RatingController::class, 'store']);
    Route::get('/analytics/revenue', [AdminController::class, 'getRevenueAnalytics'])->middleware('admin');
    Route::get('/analytics/servicemen', [AdminController::class, 'getTopServicemen'])->middleware('admin');
    Route::get('/analytics/categories', [AdminController::class, 'getTopCategories'])->middleware('admin');
});

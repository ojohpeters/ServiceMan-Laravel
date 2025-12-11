<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Try to find user by email first, then by username
        $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        Auth::login($user);

        // Priority 1: Check email verification first (applies to all users)
        if (!$user->is_email_verified) {
            // For unapproved servicemen, they'll see both messages on profile page
            if ($user->user_type === 'SERVICEMAN' && !$user->is_approved) {
                return redirect()->route('pending-verification')
                    ->with('warning', 'Please verify your email address. Check your inbox for the verification link.');
            }
            return redirect()->route('profile')
                ->with('error', 'Email verification required! Please verify your email address to access all features. Check your inbox for the verification link.');
        }

        // Priority 2: Check serviceman approval (only after email is verified)
        if ($user->user_type === 'SERVICEMAN' && !$user->is_approved) {
            return redirect()->route('pending-verification');
        }

        // User is verified and (if serviceman) approved - go to dashboard
        // Admins should go to /admin/dashboard instead of /dashboard
        if ($user->isAdmin()) {
            return redirect()->intended('/admin/dashboard');
        }
        return redirect()->intended('/dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string|max:20',
            'user_type' => 'required|in:CLIENT,SERVICEMAN',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255'
        ]);

        // Add conditional validation for serviceman
        if ($request->user_type === 'SERVICEMAN') {
            // Category is now optional for servicemen - admin can assign later
            $validator->sometimes('category_id', 'nullable|exists:categories,id', function ($input) {
                return $input->user_type === 'SERVICEMAN';
            });
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Generate email verification token
        $verificationToken = Str::random(64);
        
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'user_type' => $request->user_type,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'is_email_verified' => false,
            'email_verified_at' => null,
            'email_verification_token' => $verificationToken,
            'is_approved' => $request->user_type !== 'SERVICEMAN', // Servicemen need approval
            'approved_at' => $request->user_type !== 'SERVICEMAN' ? now() : null
        ]);

        // Create profile based on user type
        if ($request->user_type === 'CLIENT') {
            $user->clientProfile()->create([
                'phone_number' => $request->phone_number ?? '',
                'address' => $request->address ?? ''
            ]);
        } elseif ($request->user_type === 'SERVICEMAN') {
            // Custom category feature disabled - admin will assign category after review
            // If serviceman doesn't select a category, admin will assign one during approval
            
            $user->servicemanProfile()->create([
                'category_id' => $request->category_id,
                'bio' => $request->bio ?? '',
                'experience_years' => $request->experience_years ?? '',
                'skills' => $request->skills ?? '',
                'is_available' => $request->filled('category_id') ? true : false, // Only available if category selected
            ]);
        }

        // Send verification email
        $verificationUrl = url("/verify-email/{$verificationToken}");
        Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user, $verificationUrl));

        Auth::login($user);

        // Different messages based on user type
        if ($request->user_type === 'SERVICEMAN') {
            if ($request->filled('custom_category')) {
                $message = 'Registration successful! Please verify your email. Your account is pending admin approval. Your custom service category request has been sent for review. You will be contacted once approved for physical verification.';
            } else {
                $message = 'Registration successful! Please verify your email. Your account is pending admin approval. Admin will review your profile and contact you for physical verification. You cannot login until approved.';
            }
            
            // Notify admin about new serviceman registration (sends email + creates notification)
            $this->notificationService->notifyAdmins(
                'NEW_SERVICEMAN_REGISTRATION',
                '👨‍🔧 New Serviceman Registration',
                "New serviceman {$user->full_name} ({$user->email}) has registered and needs approval. Please review their profile at: " . url("/admin/pending-servicemen"),
                null, // No service request
                ['serviceman_id' => $user->id, 'serviceman_name' => $user->full_name, 'serviceman_email' => $user->email]
            );
        } else {
            $message = 'Registration successful! Please check your email to verify your account.';
        }

        // Redirect to profile page for unverified users (instead of dashboard)
        // This prevents redirect loops with the email verification middleware
        if (!$user->is_email_verified) {
            return redirect()->route('profile')->with('success', $message);
        }

        // Admins should go to /admin/dashboard
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard')->with('success', $message);
        }
        return redirect('/dashboard')->with('success', $message);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Security: Always show success message, don't reveal if email exists
        // Only send email if user exists (silent check)
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $token = Str::random(64);
            // Store token in cache for 1 hour
            cache()->put('password_reset_' . $user->id, $token, 3600);
            $this->sendPasswordResetEmail($user, $token);
        }

        // Always return the same success message for security
        return back()->with('status', 'If the email address exists in our system, a password reset link has been sent.');
    }

    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();
        $storedToken = cache()->get('password_reset_' . $user->id);

        if (!$storedToken || $storedToken !== $request->token) {
            return back()->withErrors(['token' => 'Invalid or expired token'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);
        cache()->forget('password_reset_' . $user->id);

        return redirect('/login')->with('status', 'Password reset successfully!');
    }

    private function sendPasswordResetEmail($user, $token)
    {
        try {
            $resetUrl = url('/reset-password/' . $token . '?email=' . $user->email);
            
            // Validate email address
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                \Log::error('Invalid email address for password reset', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return;
            }
            
            // Send email synchronously (not queued) for immediate delivery
            Mail::to($user->email)->send(new \App\Mail\ResetPasswordEmail($user, $resetUrl));
            
            \Log::info('Password reset email sent successfully', [
                'to' => $user->email,
                'user_id' => $user->id,
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email', [
                'to' => $user->email,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to see the error in development
            if (config('app.debug')) {
                throw $e;
            }
        }
    }

    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Invalid or expired verification link. Please request a new one.');
        }

        if ($user->is_email_verified) {
            // Admins should go to /admin/dashboard
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard')->with('info', 'Email already verified!');
            }
            return redirect('/dashboard')->with('info', 'Email already verified!');
        }

        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null
        ]);

        // Admins should go to /admin/dashboard
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard')->with('success', 'Email verified successfully! Welcome to ServiceMan!');
        }
        return redirect('/dashboard')->with('success', 'Email verified successfully! Welcome to ServiceMan!');
    }

    /**
     * Resend verification email for authenticated users
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized. Please login first.'], 401);
            }
            return redirect('/login')->with('error', 'Please login to resend verification email.');
        }

        // Check if email is already verified
        if ($user->is_email_verified) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Your email is already verified.', 'info' => true], 200);
            }
            return back()->with('info', 'Your email is already verified.');
        }

        // Generate new verification token (replaces old one if expired)
        $verificationToken = \Illuminate\Support\Str::random(64);
        
        try {
            $user->update([
                'email_verification_token' => $verificationToken
            ]);

            // Send verification email
            $verificationUrl = url("/verify-email/{$verificationToken}");
            
            \Log::info('Attempting to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'url' => $verificationUrl
            ]);
            
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user, $verificationUrl));
            
            \Log::info('Verification email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification email sent successfully! Please check your inbox at ' . $user->email,
                    'email' => $user->email
                ], 200);
            }

            return back()->with('success', 'Verification email sent successfully! Please check your inbox at ' . $user->email);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Failed to send verification email: ' . $e->getMessage() . '. Please check your email configuration or contact support.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            return back()->with('error', 'Failed to send verification email. Please try again later or contact support.');
        }
    }

    public function showPendingVerification()
    {
        $user = auth()->user();
        
        // Must be authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // If not a serviceman, redirect to appropriate page
        if ($user->user_type !== 'SERVICEMAN') {
            // Check email verification first
            if (!$user->is_email_verified) {
                return redirect()->route('profile')
                    ->with('info', 'This page is only for servicemen awaiting approval.');
            }
            // Admins should go to /admin/dashboard
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            }
            return redirect()->route('dashboard');
        }

        // If serviceman is already approved, redirect to dashboard
        if ($user->is_approved) {
            // Check email verification first
            if (!$user->is_email_verified) {
                return redirect()->route('profile')
                    ->with('info', 'Your account is approved. Please verify your email to continue.');
            }
            // Admins should go to /admin/dashboard
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            }
            return redirect()->route('dashboard');
        }

        // User is an unapproved serviceman - show pending page
        return view('auth.pending-verification');
    }
}


<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

        // Check if serviceman is approved
        if ($user->user_type === 'SERVICEMAN' && !$user->is_approved) {
            // Login them to show the pending verification page
            Auth::login($user);
            return redirect()->route('pending-verification');
        }

        Auth::login($user);

        // If email is not verified, redirect to profile page to show verification message
        if (!$user->is_email_verified) {
            return redirect()->route('profile')
                ->with('error', 'Email verification required! Please verify your email address to access all features. Check your inbox for the verification link.');
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
                'address' => $request->address ?? ''
            ]);
        } elseif ($request->user_type === 'SERVICEMAN') {
            // If custom category provided, create a custom service request
            if ($request->filled('custom_category') && !$request->filled('category_id')) {
                \App\Models\CustomServiceRequest::create([
                    'serviceman_id' => $user->id,
                    'service_name' => $request->custom_category,
                    'service_description' => $request->bio ?? 'Custom service category requested during registration',
                    'why_needed' => 'Requested during registration',
                    'status' => 'PENDING',
                ]);
                
                // Notify admin
                AppNotification::create([
                    'user_id' => null,
                    'service_request_id' => null,
                    'type' => 'CUSTOM_SERVICE_REQUEST',
                    'title' => '🆕 New Custom Service Request (Registration)',
                    'message' => "New serviceman {$user->full_name} requested custom category '{$request->custom_category}' during registration. Please review and assign.",
                    'is_read' => false,
                ]);
            }
            
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
            
            // Notify admin about new serviceman registration
            AppNotification::create([
                'user_id' => null, // Admin notification
                'service_request_id' => null,
                'type' => 'NEW_SERVICEMAN_REGISTRATION',
                'title' => '👨‍🔧 New Serviceman Registration',
                'message' => "New serviceman {$user->full_name} ({$user->email}) has registered and needs approval. Please review their profile.",
                'is_read' => false,
            ]);
        } else {
            $message = 'Registration successful! Please check your email to verify your account.';
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
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();
        $token = Str::random(64);

        // Store token in cache for 1 hour
        cache()->put('password_reset_' . $user->id, $token, 3600);

        $this->sendPasswordResetEmail($user, $token);

        return back()->with('status', 'Password reset email sent!');
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
            Mail::to($user->email)->send(new \App\Mail\ResetPasswordEmail($user, $resetUrl));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }
    }

    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Invalid or expired verification link. Please request a new one.');
        }

        if ($user->is_email_verified) {
            return redirect('/dashboard')->with('info', 'Email already verified!');
        }

        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null
        ]);

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
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect('/login')->with('error', 'Please login to resend verification email.');
        }

        // Check if email is already verified
        if ($user->is_email_verified) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email is already verified'], 400);
            }
            return back()->with('info', 'Your email is already verified.');
        }

        // Generate new verification token (replaces old one if expired)
        $verificationToken = \Illuminate\Support\Str::random(64);
        $user->update([
            'email_verification_token' => $verificationToken
        ]);

        // Send verification email
        try {
            $verificationUrl = url("/verify-email/{$verificationToken}");
            Mail::to($user->email)->send(new \App\Mail\VerifyEmail($user, $verificationUrl));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to send verification email. Please try again later.'], 500);
            }
            return back()->with('error', 'Failed to send verification email. Please try again later or contact support.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification email sent successfully! Please check your inbox.',
                'email' => $user->email
            ]);
        }

        return back()->with('success', 'Verification email sent successfully! Please check your inbox at ' . $user->email);
    }

    public function showPendingVerification()
    {
        // Only show this page to unapproved servicemen
        if (!auth()->check() || auth()->user()->user_type !== 'SERVICEMAN' || auth()->user()->is_approved) {
            return redirect('/dashboard');
        }

        return view('auth.pending-verification');
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:CLIENT,SERVICEMAN',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255'
        ]);

        // Add conditional validation for serviceman
        if ($request->user_type === 'SERVICEMAN') {
            $validator->sometimes('category_id', 'required|exists:categories,id', function ($input) {
                return $input->user_type === 'SERVICEMAN';
            });
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'is_email_verified' => true,
            'email_verified_at' => now()
        ]);

        // Create profile based on user type
        if ($request->user_type === 'CLIENT') {
            $user->clientProfile()->create([
                'phone_number' => $request->phone_number ?? '',
                'address' => $request->address ?? ''
            ]);
        } elseif ($request->user_type === 'SERVICEMAN') {
            $user->servicemanProfile()->create([
                'category_id' => $request->category_id,
                'phone_number' => $request->phone_number ?? '',
                'bio' => $request->bio ?? '',
                'experience_years' => $request->experience_years ?? '',
                'skills' => $request->skills ?? '',
                'is_available' => true,
                'hourly_rate' => $request->hourly_rate ?? null
            ]);
        }

        // Send verification email
        $this->sendVerificationEmail($user);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Try to find user by email first, then by username
        $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user
        ]);
    }

    public function me()
    {
        $user = Auth::guard('api')->user();
        $user->load(['clientProfile', 'servicemanProfile']);
        
        return response()->json($user);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token could not be refreshed'], 401);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|exists:users,id',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::find($request->uid);
        
        // Simple token verification (in production, use proper token system)
        if ($request->token === 'valid_token_' . $user->id) {
            $user->update(['is_email_verified' => true]);
            return response()->json(['message' => 'Email verified successfully']);
        }

        return response()->json(['error' => 'Invalid token'], 400);
    }

    public function resendVerificationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->is_email_verified) {
            return response()->json(['message' => 'Email is already verified'], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json(['message' => 'Verification email sent']);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();
        $token = Str::random(64);

        // Store token in cache for 1 hour
        cache()->put('password_reset_' . $user->id, $token, 3600);

        $this->sendPasswordResetEmail($user, $token);

        return response()->json(['message' => 'Password reset email sent']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|exists:users,id',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::find($request->uid);
        $storedToken = cache()->get('password_reset_' . $user->id);

        if (!$storedToken || $storedToken !== $request->token) {
            return response()->json(['error' => 'Invalid or expired token'], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);
        cache()->forget('password_reset_' . $user->id);

        return response()->json(['message' => 'Password reset successfully']);
    }

    private function sendVerificationEmail($user)
    {
        try {
            $token = 'valid_token_' . $user->id; // Simple token for demo
            $url = url('/api/auth/verify-email?uid=' . $user->id . '&token=' . $token);

            Mail::raw("Please verify your email by clicking this link: {$url}", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email - ServiceMan');
            });
        } catch (\Exception $e) {
            // Log the error but don't fail the registration
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }
    }

    private function sendPasswordResetEmail($user, $token)
    {
        try {
            $url = url('/api/auth/reset-password?uid=' . $user->id . '&token=' . $token);

            Mail::raw("Reset your password by clicking this link: {$url}", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Reset Your Password - ServiceMan');
            });
        } catch (\Exception $e) {
            // Log the error but don't fail the password reset request
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }
    }
}